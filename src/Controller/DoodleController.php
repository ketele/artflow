<?php

namespace App\Controller;

use App\Entity\Doodle;
use App\Entity\DoodleComment;
use App\Entity\DoodleStatus;
use App\Form\DoodleCommentFormType;
use App\Form\DoodleFormType;
use App\Repository\AdminRepository;
use App\Repository\DoodleCommentRepository;
use App\Repository\DoodleRepository;
use App\Image\Glide;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use League\Glide\ServerFactory;
use League\Glide\Responses\SymfonyResponseFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

class DoodleController extends AbstractController
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route(
     *     "/{_locale<%app.supported_locales%>}/doodle/{id<\d+>}",
     *     name="doodle",
     *     defaults={"id": null}
     * )
     * @param int|null $id
     * @param DoodleRepository $doodleRepository
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function index(?int $id, DoodleRepository $doodleRepository)
    {
        $doodle_coordinates_json = '';

        if (is_numeric($id)) {
            $doodle = $doodleRepository->findOne($id);
            if ($doodle) {
                $doodle_coordinates = $doodle->getCoordinates();
                $doodle_coordinates_json = json_encode($doodle_coordinates);
            }
        }

        return $this->render('doodle/index.html.twig', [
            'controller_name' => 'DoodleController',
            'doodle_coordinates_json' => $doodle_coordinates_json,
            'id' => $id
        ]);
    }

    /**
     * @Route("/api/store_doodle_temp", name="store_doodle_temp")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function storeDoodleTempApi(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Error'
            ],
                400);
        }

        $filesystem = new Filesystem();

        $img = $request->get('imgBase64');
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $fileName = uniqid() . '.png';
        $tempDir = md5(random_int(0, 1000) . date('U'));
        $path = sys_get_temp_dir() . '/' . $tempDir;

        try {
            $filesystem->mkdir($path);
            $filesystem->dumpFile($path . '/' . $fileName, $data);
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at " . $exception->getPath();
        }

        $jsonData['status'] = true;
        $jsonData['tempDir'] = $tempDir;

        return new JsonResponse($jsonData);
    }

    /**
     * @Route(
     *     "/{_locale<%app.supported_locales%>}/doodle/view/{id}",
     *     name="doodle_view"
     * )
     * @param int $id
     * @param DoodleRepository $doodleRepository
     * @param DoodleCommentRepository $doodleCommentRepository
     * @param Request $request
     * @param NotificationRepository $notificationRepository
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function view(int $id,
                         DoodleRepository $doodleRepository,
                         DoodleCommentRepository $doodleCommentRepository,
                         Request $request,
                         NotificationRepository $notificationRepository
    )
    {
        $doodleComment = new DoodleComment();
        $doodle = $doodleRepository->findOne($id);
        $user = $this->getUser();

        $doodle->setViews($doodle->getViews() + 1);
        $doodleRepository->save($doodle);

        $doodles = $doodleRepository->findRecommended($id);

        $doodleComment->setDoodle($doodle);
        $commentForm = $this->createForm(DoodleCommentFormType::class, $doodleComment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $form_data = $request->get('doodle_comment_form');

            $doodleComment->setUser($user);
            $doodleCommentRepository->save($doodleComment);

            $this->addFlash('success', $this->translator->trans('Your comment has been added'));

            if ($doodle->getUser() != $user) {
                $doodleUser = $doodle->getUser();
                $notificationRepository->addNotification([
                    'users' => [$doodleUser],
                    'content' => $this->translator->trans('You have new comment in doodle', [], null, $doodleUser->getLocale())
                        . ' "' . $doodle->getTitle() . '"
                    
                    "' . $form_data['content'] . '"
                    - ' . $user->getUsername()
                ]);
            }

            return $this->redirectToRoute('doodle_view',
                ['id' => $doodle->getId()]);
        } else {
            $doodleComments = $doodleCommentRepository->findRootByDoodleId($id);

            return $this->render('doodle/view.html.twig', [
                'controller_name' => 'DoodleController',
                'doodle' => $doodle,
                'is_rejected' => $doodle->getStatus()->getId() == DoodleStatus::STATUS_REJECTED,
                'is_new' => $doodle->getStatus()->getId() == DoodleStatus::STATUS_NEW,
                'doodles' => $doodles,
                'commentForm' => $commentForm->createView(),
                'doodleComments' => $doodleComments,
            ]);
        }
    }

    /**
     * @Route("/api/doodle/comment/{id<\d+>}/manage", name="doodle_comment_ajax", methods={"GET"})
     * @param int $id
     * @param DoodleRepository $doodleRepository
     * @param DoodleCommentRepository $doodleCommentRepository
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function doodleCommentAjax(
        int $id,
        DoodleRepository $doodleRepository,
        DoodleCommentRepository $doodleCommentRepository
    ): JsonResponse
    {
        $parentDoodleComment = $doodleCommentRepository->findOneBy(['id' => $id]);
        $doodleComment = new DoodleComment();

        $doodle = $doodleRepository->findOne($parentDoodleComment->getId());
        $doodleComment->setDoodle($doodle);
        $doodleComment->setParent($parentDoodleComment);
        $commentForm = $this->createForm(DoodleCommentFormType::class, $doodleComment);
        $content = $this->renderView('doodle/comment_form.html.twig', ['form' => $commentForm->createView()]);

        return new JsonResponse(['content' => $content]);
    }

    /**
     * @Route(
     *     "/{_locale<%app.supported_locales%>}/doodle/gallery/{order<createdAt|popularity>}/{id<\d+>}",
     *     name="doodle_gallery",
     *     defaults={"order": "popularity","id": null}
     * )
     * @param string $order
     * @param int|null $id
     * @param DoodleRepository $doodleRepository
     * @return Response
     */
    public function gallery(string $order, ?int $id, DoodleRepository $doodleRepository)
    {
        if (is_numeric($id)) {
            $doodles = $doodleRepository->findSimilar($id, [['d.' . $order, 'DESC']]);
        } else {
            $doodles = $doodleRepository->findPublished([['d.' . $order, 'DESC']]);
        }

        return $this->render('doodle/gallery.html.twig', [
            'controller_name' => 'DoodleController',
            'doodles' => $doodles,
            'id' => $id,
        ]);
    }

    /**
     * @Route("/{_locale<%app.supported_locales%>}/add_doodle", methods={"POST","GET"})
     * @param Request $request
     * @param NotifierInterface $notifier
     * @param string $doodleDir
     * @param string $doodleFolder
     * @param DoodleRepository $doodleRepository
     * @return Response
     */
    public function addDoodle(Request $request, NotifierInterface $notifier, string $doodleDir, string $doodleFolder,
                               DoodleRepository $doodleRepository
    )
    {
        if ($this->isGranted('ROLE_USER') == false) {
            $this->addFlash('warning', $this->translator->trans('You need to be logged to save doodle'));
            return $this->redirectToRoute('app_login');
        }

        $tempDir = $request->get('tempDir');
        $sourceDoodle = $request->get('sourceDoodle');
        $sourceDoodleId = $request->get('sourceDoodleId');

        $filesystem = new Filesystem();
        $finder = new Finder();
        $fileName = null;
        $doodle = new Doodle();
        $glide = new Glide();
        $user = $this->getUser();

        $defaultData['tempDir'] = $tempDir;
        $defaultData['sourceDoodle'] = $sourceDoodle;
        $defaultData['sourceDoodleId'] = $sourceDoodleId;

        $form = $this->createForm(DoodleFormType::class, $defaultData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $form_data = $request->get('doodle_form');
            $sourceDoodle = json_decode(urldecode($form_data['sourceDoodle']), true);
            $sourceDoodleId = $form_data['sourceDoodleId'];
            $tempPath = sys_get_temp_dir() . '/' . $form_data['tempDir'] . '/';
            $finder->files()->in($tempPath);

            if ($finder->hasResults()) {
                $iterator = $finder->getIterator();
                $iterator->rewind();
                $firstFile = $iterator->current();
                $fileName = $firstFile->getRelativePathname();
            }

            $doodle->setUser($user);
            $doodle->setFileName($fileName);
            $doodle->setUserName($user->getUsername());
            $doodle->setDescription($form_data['description']);
            $doodle->setTitle($form_data['title']);
            if (is_numeric($sourceDoodleId)) {
                $doodle->setSourceDoodleId($sourceDoodleId);
            }
            $doodle->setCoordinates($sourceDoodle);
            $doodleRepository->save($doodle);
            $doodlePath = $doodleDir . '/' . $doodleFolder . $doodle->getId();
            try {
                $filesystem->mirror($tempPath, $doodlePath);
            } catch (FileException $e) {
                return new Response($e->getMessage());
            }

            $notifier->send(new Notification('Your doodle will be posted after moderation.', ['browser']));

            return $this->redirectToRoute('doodle_view', ['id' => $doodle->getId()]);
        } else {
            if (empty($tempDir)) {
                return new Response('Doodle data is empty');
            }

            $tempPath = sys_get_temp_dir() . '/' . $tempDir . '/';
            $finder->files()->in($tempPath);

            if ($finder->hasResults()) {
                $iterator = $finder->getIterator();
                $iterator->rewind();
                $firstFile = $iterator->current();
                $fileName = $firstFile->getRelativePathname();
            }

            $doodle->setUrl($glide->generateUrl($tempDir, $fileName, []));

            return $this->render('doodle/add.html.twig', [
                'controller_name' => 'DoodleController',
                'form' => $form->createView(),
                'doodle' => $doodle,
            ]);
        }
    }

    /**
     * @Route("/img/doodle/{folder}/{file_name}", name="doodle_img", methods={"GET"})
     * @param string $folder
     * @param string $file_name
     * @param string $doodleDir
     * @param string $doodleCache
     * @param Request $request
     * @return mixed|Response
     */
    public function doodleImg(string $folder, string $file_name, string $doodleDir, string $doodleCache, Request $request)
    {
        $glide = new Glide();
        $path = $folder . '/' . $file_name;

        try {
            $glide->validateRequest($path, $request->query->all());

            $server = ServerFactory::create([
                'source' => $doodleDir,
                'cache' => $doodleCache,
                'response' => new SymfonyResponseFactory()
            ]);

            return $server->getImageResponse($path, $request->query->all());
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }
    }

    /**
     * @Route("/temp_img/doodle/{folder}/{file_name}", name="temp_doodle_img", methods={"GET"})
     * @param string $folder
     * @param string $file_name
     * @param string $tempCache
     * @param Request $request
     * @return mixed|Response
     */
    public function doodleTempImg(string $folder, string $file_name, string $tempCache, Request $request)
    {
        $glide = new Glide();
        $data = $request->query->all();
        $path = $folder . '/' . $file_name;

        try {
            $glide->validateRequest($path, $data);
            $server = ServerFactory::create([
                'source' => sys_get_temp_dir(),
                'cache' => $tempCache,
                'response' => new SymfonyResponseFactory()
            ]);

            return $server->getImageResponse($path, $data);
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }
    }

    /**
     * @Route(
     *     "/{_locale<%app.supported_locales%>}/user/doodle/edit/{id}",
     *     name="user_doodle_edit"
     * )
     * @param int $id
     * @param DoodleRepository $doodleRepository
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function editDoodle(
        int $id,
        DoodleRepository $doodleRepository,
        Request $request
    )
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $doodle = $doodleRepository->findOne($id);
        $user = $this->getUser();

        if ($user != $doodle->getUser()) {
            return $this->redirectToRoute('doodle_view',
                ['id' => $doodle->getId()]);
        }

        $form = $this->createForm(DoodleFormType::class, $doodle);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $form_data = $request->get('doodle_form');

            $doodle->setDescription($form_data['description']);
            $doodle->setTitle($form_data['title']);
            $doodleRepository->save($doodle);

            return $this->redirectToRoute('doodle_view',
                ['id' => $doodle->getId()]);
        } else {
            return $this->render('doodle/edit.html.twig', [
                'controller_name' => 'DoodleController',
                'doodle' => $doodle,
                'user' => $user,
                'form' => $form->createView(),
            ]);
        }
    }

    /**
     * @Route(
     *     "/{_locale<%app.supported_locales%>}/user/doodle/delete/{id}/{confirmed}",
     *     name="user_doodle_delete",
     *     defaults={"confirmed": false}
     * )
     * @param int $id
     * @param bool $confirmed
     * @param DoodleRepository $doodleRepository
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function deleteDoodle(
        int $id,
        bool $confirmed,
        DoodleRepository $doodleRepository
    )
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $doodle = $doodleRepository->findOne($id);
        $user = $this->getUser();

        if ($user != $doodle->getUser()) {
            return $this->redirectToRoute('doodle_view',
                ['id' => $doodle->getId()]);
        }

        if ($confirmed) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($doodle);
            $entityManager->flush();

            return $this->redirectToRoute('user_doodle_gallery',
                ['username' => $user->getUsername()]);
        } else {
            return $this->render('doodle/delete.html.twig', [
                'controller_name' => 'DoodleController',
                'doodle' => $doodle,
                'user' => $user,
            ]);
        }
    }

    /**
     * @Route(
     *     "/{_locale<%app.supported_locales%>}/user/doodle/gallery/{username}/{order<createdAt|popularity>}",
     *     name="user_doodle_gallery",
     *     defaults={"order": "popularity","id": null}
     * )
     * @param string $username
     * @param string $order
     * @param DoodleRepository $doodleRepository
     * @param AdminRepository $adminRepository
     * @return Response
     */
    public function userGallery(
        string $username,
        string $order,
        DoodleRepository $doodleRepository,
        AdminRepository $adminRepository
    )
    {
        $user = $adminRepository->findOneBy(['username' => $username]);

        $doodles = $doodleRepository->findByUser($user, [['d.' . $order, 'DESC']]);

        return $this->render('user/doodle_gallery.html.twig', [
            'controller_name' => 'DoodleController',
            'doodles' => $doodles,
        ]);
    }
}
