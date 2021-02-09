<?php

namespace App\Controller\Admin;

use App\Repository\DoodleRepository;
use App\Repository\DoodleStatusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DoodleController extends AbstractController
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route(
     *     "admin/doodles/{id<\d+>}",
     *     name="admin_doodle_gallery",
     *     defaults={"id": null}
     * )
     * @param int|null $id
     * @param DoodleRepository $doodleRepository
     * @param string $doodleFolder
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function gallery(?int $id,
                            DoodleRepository $doodleRepository,
                            DoodleStatusRepository $doodleStatusRepository,
                            string $doodleFolder,
                            Request $request
    )
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $order = $request->get('order', 'createdAt');
        $status = $request->get('status');
        $where = [];

        $parameters = [];

        if (is_numeric($status)) {
            $where[] = 'd.status = ' . $status;
        }

        if (is_numeric($id)) {
            $rootDoodle = $doodleRepository->findOne($id);
            $ipTree = $rootDoodle->getIpTree();
            $rootId = explode('.', $ipTree)[0];
            $where[] = '( d.id = :doodleId OR d.ipTree LIKE :doodleIdBegin OR d.ipTree LIKE :doodleIdInner OR d.ipTree LIKE :doodleIdEnd)';
            $parameters['doodleId'] = $rootId;
            $parameters['doodleIdBegin'] = $rootId . '.%';
            $parameters['doodleIdInner'] = '%.' . $rootId . '.%';
            $parameters['doodleIdEnd'] = '%.' . $rootId;
        }

        $doodles = $doodleRepository->getDoodles([
            'order' => [['d.' . $order, 'DESC']],
            'maxResults' => 50,
            'where' => $where,
            'parameters' => $parameters,
        ]);

        $doodleStatuses = $doodleStatusRepository->getStatuses();

        return $this->render('admin/doodle/index.html.twig', [
            'controller_name' => 'DoodleController',
            'doodles' => $doodles,
            'doodle_statuses' => $doodleStatuses,
            'id' => $id,
            'status' => $status,
        ]);
    }

    /**
     * @Route("/api/doodle/status/{id<\d+>}/edit", name="admin_status_change_modal_view", methods={"GET"}, defaults={"id": null})
     * @param Request $request
     * @throws \Exception
     */

    public function statusChangeModalView(int $id, Request $request, DoodleRepository $doodleRepository,
                                          DoodleStatusRepository $doodleStatusRepository): JsonResponse
    {
        $error = [];
        $response = new JsonResponse();

        if (!$this->isGranted('ROLE_ADMIN')) {
            $error[] = $this->translator->trans("You can't edit this status");
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        if (!is_numeric($id)) {
            $error[] = 'Wrong id';
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            $doodle = $doodleRepository->findOne($id);
        }

        $doodleStatuses = $doodleStatusRepository->getStatuses();

        return new JsonResponse(['content' => $this->renderView('admin/doodle/status_change_modal.html.twig', [
            'error' => $error,
            'id' => $id,
            'doodle' => $doodle,
            'doodleStatuses' => $doodleStatuses,
        ])]);
    }

    /**
     * @Route("api/doodle/status/{id<\d+>}", name="admin_status_change_ajax", methods={"PUT|POST"}, defaults={"id": null}))
     */

    public function statusChangeAjax(?int $id, Request $request, DoodleRepository $doodleRepository,
                                     DoodleStatusRepository $doodleStatusRepository
    ): JsonResponse
    {
        $error = [];
        $response = new JsonResponse();

        if (!$this->isGranted('ROLE_ADMIN')) {
            $error[] = $this->translator->trans("You can't edit this status");
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $statusId = $request->get('statusId');

        if (!is_numeric($id)) {
            $error[] = 'Wrong id';
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        if (!is_numeric($statusId)) {
            $error[] = 'Wrong status id';
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        if (empty($error)) {
            $doodle = $doodleRepository->findOne($id);
            $status = $doodleStatusRepository->findOne($statusId);
            $doodle->setStatus($status);
            $doodleRepository->save($doodle);
        }

        return new JsonResponse(['error' => $error]);
    }
}
