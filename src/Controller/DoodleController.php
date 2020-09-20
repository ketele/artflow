<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DoodleController extends AbstractController
{
    /**
     * @Route("/{_locale<%app.supported_locales%>}/doodle", name="doodle")
     */
    public function index()
    {
        return $this->render('doodle/index.html.twig', [
            'controller_name' => 'DoodleController',
        ]);
    }
}
