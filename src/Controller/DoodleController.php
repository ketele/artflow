<?php

namespace App\Controller;

use App\Entity\Doodle;
use App\Entity\DoodleStatus;
use App\Repository\DoodleRepository;
use App\Security\Glide;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
     * @Route("/{_locale<%app.supported_locales%>}/doodle", name="doodle")
     */
    public function index() {
        return $this->render('doodle/index.html.twig', [
            'controller_name' => 'DoodleController',
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
     * @Route("/{_locale<%app.supported_locales%>}/doodle/view/{id}", name="doodle_view")
     * @param int $id
     * @param string $doodleFolder
     * @param DoodleRepository $doodleRepository
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function view(int $id, string $doodleFolder, DoodleRepository $doodleRepository)
    {
        $glide = new Glide();
        $doodle = $doodleRepository->findOne($id);
        $file_name = $doodle->getFileName();

        return $this->render('doodle/view.html.twig', [
            'controller_name' => 'DoodleController',
            'description' => $doodle->getDescription(),
            'user_name' => $doodle->getUserName(),
            'status_rejected' => $doodle->getStatus()->getId() == DoodleStatus::STATUS_REJECTED,
            'status_new' => $doodle->getStatus()->getId() == DoodleStatus::STATUS_NEW,
            'file_url' => $glide->generateUrl($doodleFolder . $id, $file_name, []),
        ]);
    }

    /**
     * @Route("/{_locale<%app.supported_locales%>}/add_doodle", methods={"POST","GET"})
     * @param Request $request
     * @param NotifierInterface $notifier
     * @param EntityManagerInterface $entityManager
     * @param string $doodleDir
     * @param string $doodleFolder
     * @return Response
     */
    public function add_doodle(Request $request, NotifierInterface $notifier, EntityManagerInterface $entityManager, string $doodleDir, string $doodleFolder)
    {
        $tempDir = $request->get('temp_dir');
        $sourceDoodle = $request->get('source_doodle');

        $filesystem = new Filesystem();
        $finder = new Finder();
        $fileName = null;
        $doodle = new Doodle();
        $glide = new Glide();

        $defaultData['tempDir'] = $tempDir;
        $defaultData['sourceDoodle'] = $sourceDoodle;

        //https://about.deviantart.com/policy/submission/
        //https://about.deviantart.com/policy/service/

        $form = $this->createFormBuilder($defaultData)
            ->add('userName', TextType::class)
            ->add('description', TextareaType::class)
            ->add('tempDir', HiddenType::class)
            ->add('sourceDoodle', HiddenType::class)
            /*->add('agreeSubmissionPolicy', CheckboxType::class,
                [
                    'label'  => $this->translator->trans('I have read and agree to the Submission Policy',
                        [
                            'submission_policy_link' => '<a href="' . $this->generateUrl('submission_policy') . '" target="_blank">
                            ' . $this->translator->trans('Submission Policy') . '
                            </a>'
                        ]
                    ),
                    'label_html' => true,
                    'required' => true,
                    'mapped' => false
                ]
            )*/
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

            if ($finder->hasResults()) {
                $iterator = $finder->getIterator();
                $iterator->rewind();
                $firstFile = $iterator->current();
                $fileName = $firstFile->getRelativePathname();
            }

            $metadata = $entityManager->getClassMetadata(get_class($doodle));
            $doodle->setFileName($fileName);
            $doodle->setUserName($form_data['userName']);
            $doodle->setDescription($form_data['description']);
            $doodle->setCoordinates($sourceDoodle);
            $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_AUTO);
            $entityManager->persist($doodle);
            $entityManager->flush();
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
