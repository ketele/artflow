<?php

namespace App\Controller;

use App\Repository\DoodleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomePageController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function indexNoLocale()
    {
        return $this->redirectToRoute('home', ['_locale' => 'en']);
    }

    /**
     * @Route("/{_locale<%app.supported_locales%>}/", name="home")
     */
    public function index(DoodleRepository $doodleRepository, string $doodleDir, string $doodleFolder)
    {
        $doodles = $doodleRepository->findPublished(null, 3);

        $new_doodles = $doodleRepository->findPublished([['d.createdAt', 'DESC']], 3);

        return $this->render('home_page/index.html.twig', [
            'controller_name' => 'HomePageController',
            'doodles' => $doodles,
            'new_doodles' => $new_doodles,
            'doodleDir' => $doodleDir,
            'doodleFolder' => $doodleFolder,
        ]);
    }

    /**
     * @Route("/{_locale<%app.supported_locales%>}/policy/submission", name="submission_policy")
     */
    public function submission_policy()
    {
        return $this->render('home_page/submission_policy.html.twig', [
            'controller_name' => 'HomePageController',
        ]);
    }

    /**
     * @Route("/{_locale<%app.supported_locales%>}/policy/service", name="terms_of_service")
     */
    public function terms_of_service()
    {
        return $this->render('home_page/terms_of_service.html.twig', [
            'controller_name' => 'HomePageController',
            'violations_mail' => $_ENV['VIOLATIONS_MAIL'],
        ]);
    }

    /**
     * @Route("/{_locale<%app.supported_locales%>}/policy/privacy", name="privacy_policy")
     */
    public function privacy_policy()
    {
        return $this->render('home_page/privacy_policy.html.twig', [
            'controller_name' => 'HomePageController',
            'violations_mail' => $_ENV['VIOLATIONS_MAIL'],
        ]);
    }
}
