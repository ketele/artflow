<?php

namespace App\Controller;

use App\Repository\AdminRepository;
use App\Repository\DoodleRepository;
use App\Security\Glide;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user/{username}", name="user")
     */
    public function index(
        string $username,
        AdminRepository $adminRepository,
        DoodleRepository $doodleRepository,
        string $doodleDir,
        string $doodleFolder
    ): Response
    {
        $glide = new Glide();
        $user = $adminRepository->findOneBy(['username' => $username]);

        $doodles = $doodleRepository->getDoodles(['where' => ['d.user = ' . $user->getId()]]);
        foreach($doodles AS $doodles_key => $doodle) {
            $doodle->setUrl($glide->generateUrl($doodleFolder . $doodle->getId(), $doodle->getFileName()));
        }
        $new_doodles = $doodleRepository->getDoodles([
            'where' => ['d.user = ' . $user->getId()],
            'order' => [['d.createdAt', 'DESC']],
        ]);
        foreach($new_doodles AS $doodles_key => $doodle) {
            $doodle->setUrl($glide->generateUrl($doodleFolder . $doodle->getId(), $doodle->getFileName()));
        }

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user,
            'doodles' => $doodles,
            'new_doodles' => $new_doodles,
            'doodleDir' => $doodleDir,
            'doodleFolder' => $doodleFolder,
            'glide' => $glide,
        ]);
    }
}
