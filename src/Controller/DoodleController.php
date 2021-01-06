<?php

namespace App\Controller;

use App\Entity\Doodle;
use App\Entity\DoodleComment;
use App\Entity\DoodleStatus;
use App\Form\DoodleCommentFormType;
use App\Repository\DoodleCommentRepository;
use App\Repository\DoodleRepository;
use App\Security\Glide;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
     */
    public function index(?int $id, DoodleRepository $doodleRepository) {
        $doodle_coordinates = array();
        $doodle_coordinates_json = '';

        if( is_numeric($id) ){
            $doodle = $doodleRepository->findOne($id);
            if($doodle) {
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
     * @Route("/store_doodle_temp_ajax", name="store_doodle_temp_ajax")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store_doodle_temp_ajax(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'status' => false,
                'message' => 'Error'),
                400);
        }

        $filesystem = new Filesystem();

        $img = $request->get('imgBase64');
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $file_name = uniqid() . '.png';
        $temp_dir = md5(random_int(0, 1000) . date('U'));
        $path = sys_get_temp_dir() . '/' . $temp_dir;

        try {
            $filesystem->mkdir($path);
            $filesystem->dumpFile($path . '/' . $file_name, $data);
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at " . $exception->getPath();
        }/**/

        $jsonData['status'] = true;
        $jsonData['temp_dir'] = $temp_dir;
        $jsonData['file_name'] = $file_name;

        return new JsonResponse($jsonData);
    }

    /**
     * @Route(
     *     "/{_locale<%app.supported_locales%>}/doodle/view/{id}",
     *     name="doodle_view"
     * )
     * @param int $id
     * @param string $doodleFolder
     * @param DoodleRepository $doodleRepository
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function view(int $id, string $doodleFolder,
                         DoodleRepository $doodleRepository,
                         DoodleCommentRepository $doodleCommentRepository,
                         Request $request
    )
    {
        $glide = new Glide();
        $doodleComment = new DoodleComment();
        $doodle = $doodleRepository->findOne($id);
        $fileName = $doodle->getFileName();
        $user = $this->getUser();

        $otherDoodlesCount = 3;
        $doodle->setViews($doodle->getViews() + 1);
        $doodleRepository->save($doodle);

        $doodles = $doodleRepository->getDoodles([
            'where' => [
                'd.id != :parentDoodle',
                '( d.ipTree LIKE :parentDoodleIpTreeBegin OR d.ipTree LIKE :parentDoodleIpTree )',
                'd.status = 1',
            ],
            'parameters' => [
                'parentDoodle' => '' . $id . '',
                'parentDoodleIpTreeBegin' => '%.' . $id . '.%',
                'parentDoodleIpTree' => '' . $id . '.%',
            ],
            'maxResults' => $otherDoodlesCount,
        ]);

        if(count($doodles) < $otherDoodlesCount) {
            $doodlesTemp = $doodleRepository->getDoodles([
                'select' => 'd, ABS(DATE_DIFF( d.createdAt, :parentCreatedAt )) AS HIDDEN score',
                'where' => [
                    'd.id NOT IN(:doodles)',
                    'd.status = 1',
                ],
                'parameters' => [
                    'doodles' => '' . $id . ( count($doodles) > 0 ? ',' . implode( array_map( function( $v ){ return $v->getId(); } , $doodles ) ) : '' ) . '',
                    'parentCreatedAt' => $doodle->getCreatedAt(),
                ],
                'maxResults' => $otherDoodlesCount - count($doodles),
                'order' => [['score','ASC']],
            ]);

            $doodles = array_merge($doodles, $doodlesTemp);
        }

        foreach($doodles AS $doodles_key => $d) {
            $d->setUrl($glide->generateUrl($doodleFolder . $d->getId(), $d->getFileName()));
        }

        $doodleComment->setDoodle($doodle);
        $commentForm  = $this->createForm(DoodleCommentFormType::class, $doodleComment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {

            $form_data = $request->get('doodle_comment_form');
            $doodleId = $form_data['doodleId'];

            $doodleComment->setUser($user);
            $doodleComment->setContent($form_data['content']);
            $doodleCommentRepository->save($doodleComment);

            $this->addFlash('success', $this->translator->trans('Your comment has been added'));

            return $this->redirectToRoute('doodle_view',
                ['id' => $doodle->getId()]);
        } else {
            $doodleComments = $doodleCommentRepository->getDoodlesComments(['where' => ['d.doodle = ' . $id]]);

            return $this->render('doodle/view.html.twig', [
                'controller_name' => 'DoodleController',
                'doodle' => $doodle,
                'status_rejected' => $doodle->getStatus()->getId() == DoodleStatus::STATUS_REJECTED,
                'status_new' => $doodle->getStatus()->getId() == DoodleStatus::STATUS_NEW,
                'file_url' => $glide->generateUrl($doodleFolder . $id, $fileName, []),
                'id' => $id,
                'doodles' => $doodles,
                'commentForm' => $commentForm->createView(),
                'doodleComments' => $doodleComments,
            ]);
        }
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
     * @param string $doodleFolder
     * @return Response
     */
    public function gallery(string $order, ?int $id, DoodleRepository $doodleRepository, string $doodleFolder){
        $glide = new Glide();

        $where = ['d.status = ' . DoodleStatus::STATUS_PUBLISHED];
        $parameters = [];

        if( is_numeric($id) ) {
            $rootDoodle = $doodleRepository->findOne($id);
            $ipTree = $rootDoodle->getIpTree();
            $rootId = explode('.', $ipTree)[0];
            $where[] = '( d.id = :doodleId OR d.ipTree LIKE :doodleIdBegin OR d.ipTree LIKE :doodleIdInner OR d.ipTree LIKE :doodleIdEnd)';
            $parameters['doodleId'] = $rootId;
            $parameters['doodleIdBegin'] = $rootId . '.%';
            $parameters['doodleIdInner'] = '%.' . $rootId . '.%';
            $parameters['doodleIdEnd'] = '%.' . $rootId;
        }

        $doodles = $doodleRepository->getDoodles([
            'order' => [['d.' . $order, 'DESC']],
            'maxResults' => 50,
            'where' => $where,
            'parameters' => $parameters,
        ]);

        foreach($doodles AS $doodles_key => $d) {
            $d->setUrl($glide->generateUrl($doodleFolder . $d->getId(), $d->getFileName()));
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
     * @return Response
     */
    public function add_doodle(Request $request, NotifierInterface $notifier, string $doodleDir, string $doodleFolder, DoodleRepository $doodleRepository)
    {
        if( $this->isGranted('ROLE_USER') == false ){
            $this->addFlash('warning', $this->translator->trans('You need to be logged to save doodle'));
            return $this->redirectToRoute('app_login');
        }

        $tempDir = $request->get('temp_dir');
        $sourceDoodle = $request->get('source_doodle');
        $sourceDoodleId = $request->get('source_doodle_id');

        $filesystem = new Filesystem();
        $finder = new Finder();
        $fileName = null;
        $doodle = new Doodle();
        $glide = new Glide();
        $user = $this->getUser();

        $defaultData['tempDir'] = $tempDir;
        $defaultData['sourceDoodle'] = $sourceDoodle;
        $defaultData['sourceDoodleId'] = $sourceDoodleId;

        $form = $this->createFormBuilder($defaultData)
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)
            ->add('tempDir', HiddenType::class)
            ->add('sourceDoodle', HiddenType::class)
            ->add('sourceDoodleId', HiddenType::class)
            ->add('agreeTermsOfService', CheckboxType::class,
                [
                    'label'  => $this->translator->trans('I have read and agree to the Terms of Service',
                        [
                            'terms_of_service_link' => '<a href="' . $this->generateUrl('terms_of_service') . '" target="_blank">
                            ' . $this->translator->trans('Terms of Service') . '
                            </a>'
                        ]
                    ),
                    'label_html' => true,
                    'required' => true,
                    'mapped' => false
                ]
            )
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'btn-artflow'],
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $form_data = $request->get('form');
            $tempPath = sys_get_temp_dir() . '/' . $form_data['tempDir'] . '/';
            $finder->files()->in($tempPath);
            $sourceDoodle = json_decode(urldecode($form_data['sourceDoodle']), true);
            $sourceDoodleId = $form_data['sourceDoodleId'];

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
            if( is_numeric( $sourceDoodleId ) )
                $doodle->setSourceDoodleId($sourceDoodleId);
            $doodle->setCoordinates($sourceDoodle);
            $doodleRepository->save($doodle);
            $doodlePath = $doodleDir . '/' . $doodleFolder . $doodle->getId();
            try {
                $filesystem->mirror($tempPath, $doodlePath);
            } catch (FileException $e) {
                // unable to upload the photo, give up
            }

            $notifier->send(new Notification('Your doodle will be posted after moderation.', ['browser']));

            return $this->redirectToRoute('doodle_view',
                ['id' => $doodle->getId()]);
        } else {
            if (empty($tempDir))
                return new Response('Doodle data is empty');

            $tempPath = sys_get_temp_dir() . '/' . $tempDir . '/';
            $finder->files()->in($tempPath);

            if ($finder->hasResults()) {
                $iterator = $finder->getIterator();
                $iterator->rewind();
                $firstFile = $iterator->current();
                $fileName = $firstFile->getRelativePathname();
            }

            return $this->render('doodle/add.html.twig', [
                'controller_name' => 'DoodleController',
                'form' => $form->createView(),
                'tempDir' => $tempDir,
                'file_name' => $fileName,
                'file_url' => $glide->generateUrl($tempDir, $fileName, []),
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
    public function doodle_img(string $folder, string $file_name, string $doodleDir, string $doodleCache, Request $request)
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
    public function doodle_temp_img(string $folder, string $file_name, string $tempCache, Request $request)
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
}
