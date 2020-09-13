<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DoodlesController extends AbstractController
{
    /**
     * @Route("/doodles", name="doodles")
     */
    public function index()
    {
        return $this->render('doodles/index.html.twig', [
            'controller_name' => 'DoodlesController',
        ]);
    }
}
