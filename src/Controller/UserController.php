<?php

namespace App\Controller;

use App\Entity\DoodleStatus;
use App\Repository\AdminRepository;
use App\Repository\DoodleRepository;
use App\Security\Glide;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/{_locale<%app.supported_locales%>}/user/{username}", name="user")
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

    /**
     * @Route(
     *     "/{_locale<%app.supported_locales%>}/user/{username}/doodle/gallery/{order<createdAt|popularity>}",
     *     name="user_doodle_gallery",
     *     defaults={"order": "popularity","id": null}
     * )
     * @param string $order
     * @param DoodleRepository $doodleRepository
     * @param string $doodleFolder
     * @return Response
     */
    public function gallery(
        string $username,
        string $order,
        DoodleRepository $doodleRepository,
        string $doodleFolder,
        AdminRepository $adminRepository
    ){
        $glide = new Glide();
        $user = $adminRepository->findOneBy(['username' => $username]);

        $where[] = 'd.status = ' . DoodleStatus::STATUS_PUBLISHED;
        $where[] = 'd.user = ' . $user->getId();
        $parameters = [];

        $doodles = $doodleRepository->getDoodles([
            'order' => [['d.' . $order, 'DESC']],
            'maxResults' => 50,
            'where' => $where,
            'parameters' => $parameters,
        ]);

        foreach($doodles AS $doodles_key => $d) {
            $d->setUrl($glide->generateUrl($doodleFolder . $d->getId(), $d->getFileName()));
        }

        return $this->render('user/doodle_gallery.html.twig', [
            'controller_name' => 'DoodleController',
            'doodles' => $doodles,
        ]);
    }
}
