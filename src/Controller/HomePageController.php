<?php

namespace App\Controller;

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
    public function index()
    {
        //$parameterValue = $this->getParameter('app.supported_locales');

        return $this->render('home_page/index.html.twig', [
            'controller_name' => 'HomePageController',
        ]);
    }
}
