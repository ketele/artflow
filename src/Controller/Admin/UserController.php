<?php

namespace App\Controller\Admin;

use App\Repository\AdminRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("admin/user", name="admin_user")
     */
    public function index(AdminRepository $adminRepository): Response
    {
        $users = $adminRepository->findAll();

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }
}
