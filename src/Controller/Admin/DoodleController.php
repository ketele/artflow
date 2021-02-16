<?php

namespace App\Controller\Admin;

use App\Repository\DoodleRepository;
use App\Repository\DoodleStatusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
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
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function gallery(?int $id,
                            DoodleRepository $doodleRepository,
                            DoodleStatusRepository $doodleStatusRepository,
                            Request $request
    )
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $doodles = $doodleRepository->findByFilter($request->query->all());

        $doodleStatuses = $doodleStatusRepository->findAll();

        return $this->render('admin/doodle/index.html.twig', [
            'controller_name' => 'DoodleController',
            'doodles' => $doodles,
            'doodle_statuses' => $doodleStatuses,
            'id' => $id,
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

        $doodleStatuses = $doodleStatusRepository->findAll();

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

    /**
     * @Route("admin/updatedoodlecoordinates", name="admin_update_doodle_coordinates")
     * @param DoodleRepository $doodleRepository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */

    public function updateDoodleCoordinates(DoodleRepository $doodleRepository) {
        $doodles = $doodleRepository->findAll();
        $update = false;

        foreach($doodles as $doodle) {
            if($doodle->getCoordinates()['doodle']) {
                foreach($doodle->getCoordinates()['doodle'] as $dKey => $d) {
                    if(isset($d['cp1X']) && !isset($d['cp1'])) {
                        $dCoord = $doodle->getCoordinates();
                        $update = true;
                        $dCoord['doodle'][$dKey]['cp1']['x'] = $d['cp1X'];
                        $dCoord['doodle'][$dKey]['cp1']['y'] = $d['cp1Y'];
                        $dCoord['doodle'][$dKey]['cp2']['x'] = $d['cp2X'];
                        $dCoord['doodle'][$dKey]['cp2']['y'] = $d['cp2Y'];
                        $dCoord['doodle'][$dKey]['end']['x'] = $d['x'];
                        $dCoord['doodle'][$dKey]['end']['y'] = $d['y'];
                    }
                }
            }

            if($update) {
                $doodle->setCoordinates($dCoord);
                $doodleRepository->save($doodle);
                $update = false;
            }
        }

        return $this->redirectToRoute('admin_doodle_gallery');
    }
}
