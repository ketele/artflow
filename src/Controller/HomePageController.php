<?php

namespace App\Controller;

use App\Entity\DoodleStatus;
use App\Repository\DoodleRepository;
use App\Security\Glide;
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
        $glide = new Glide();
        $doodles = $doodleRepository->getDoodles();
        foreach($doodles AS $doodles_key => $doodle) {
            $doodle->setUrl($glide->generateUrl($doodleFolder . $doodle->getId(), $doodle->getFileName()));
        }
        $new_doodles = $doodleRepository->getDoodles([
            'order' => [['d.createdAt', 'DESC']],
        ]);
        foreach($new_doodles AS $doodles_key => $doodle) {
            $doodle->setUrl($glide->generateUrl($doodleFolder . $doodle->getId(), $doodle->getFileName()));
        }

        return $this->render('home_page/index.html.twig', [
            'controller_name' => 'HomePageController',
            'doodles' => $doodles,
            'new_doodles' => $new_doodles,
            'doodleDir' => $doodleDir,
            'doodleFolder' => $doodleFolder,
            'glide' => $glide,
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
