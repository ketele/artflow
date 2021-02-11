<?php

namespace App\Controller;

use App\Repository\AdminRepository;
use App\Repository\DoodleRepository;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/{_locale<%app.supported_locales%>}/profile/{username}", name="user")
     * @param string $username
     * @param AdminRepository $adminRepository
     * @param DoodleRepository $doodleRepository
     * @param string $doodleDir
     * @param string $doodleFolder
     * @return Response
     */
    public function index(
        string $username,
        AdminRepository $adminRepository,
        DoodleRepository $doodleRepository,
        string $doodleDir,
        string $doodleFolder
    ): Response
    {
        $user = $adminRepository->findOneBy(['username' => $username]);
        $doodles = $doodleRepository->findByUser($user, null, 3);

        $new_doodles = $doodleRepository->findByUser($user, [['d.createdAt', 'DESC']], 3);

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user,
            'doodles' => $doodles,
            'new_doodles' => $new_doodles,
            'doodleDir' => $doodleDir,
            'doodleFolder' => $doodleFolder,
        ]);
    }

    /**
     * @Route("/locale/change/{locale}", name="user_locale_change")
     * @param string $locale
     * @param AdminRepository $adminRepository
     * @param Request $request
     * @param RouterInterface $router
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeLocale(
        string $locale,
        AdminRepository $adminRepository,
        Request $request,
        RouterInterface $router
    )
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->getUser();
            $user->setLocale($locale);
            $adminRepository->save($user);
        }

        $referer = $request->headers->get('referer');

        if ($referer == NULL) {
            return $this->redirectToRoute('home', ['_locale' => $locale]);
        }

        $refererPathInfo = Request::create($referer)->getPathInfo();
        $routeInfos = $router->match($refererPathInfo);

        $routeInfos['_locale'] = $locale;
        $routeName = $routeInfos['_route'];
        unset($routeInfos['_route']);

        return $this->redirectToRoute($routeName, $routeInfos);
    }

    /**
     * @Route("/{_locale<%app.supported_locales%>}/user/notifications", name="user_notifications")
     * @param NotificationRepository $notificationRepository
     * @return Response
     */
    public function notifications(
        NotificationRepository $notificationRepository
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $notifications = $notificationRepository->findBy(['user' => $user->getId()], ['createdAt' => 'DESC']);

        $view = $this->render('user/notifications.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user,
            'notifications' => $notifications,
        ]);

        $notificationRepository->setAsRead($notifications);

        return $view;
    }
}
