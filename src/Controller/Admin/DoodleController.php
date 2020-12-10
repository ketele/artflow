<?php

namespace App\Controller\Admin;

use App\Entity\Doodle;
use App\Repository\DoodleRepository;
use App\Repository\DoodleStatusRepository;
use App\Security\Glide;
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
    ){
        $order = $request->get('order', 'popularity');
        $status = $request->get('status');
        $glide = new Glide();
        $where = [];

        $parameters = [];

        if( is_numeric($status) ) {
            $where[] = 'd.status = ' . $status;
        }

        if( is_numeric($id) ) {
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

        foreach($doodles AS $doodles_key => $d) {
            $d->setUrl($glide->generateUrl($doodleFolder . $d->getId(), $d->getFileName()));
        }

        return $this->render('admin/doodle/index.html.twig', [
            'controller_name' => 'DoodleController',
            'doodles' => $doodles,
            'doodle_statuses' => $doodleStatuses,
            'id' => $id,
            'status' => $status,
        ]);
    }

    /**
     * @Route("/admin/status_change_modal_view", name="admin_status_change_modal_view")
     * @param Request $request
     * @throws \Exception
     */

    public function statusChangeModalView(Request $request, DoodleRepository $doodleRepository,
                                          DoodleStatusRepository $doodleStatusRepository)
    {
        $error = [];
        $jsonData['status'] = true;

        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'status' => false,
                'message' => 'Error! Not Xml Http Request'),
                400);
        }

        $id = $request->get('id');

        if( !is_numeric($id) ){
            $jsonData['status'] = false;
            $error[] = 'Wrong id';
        }else{
            $doodle = $doodleRepository->findOne($id);
        }

        $doodleStatuses = $doodleStatusRepository->getStatuses();

        $jsonData['content'] = $this->renderView('admin/doodle/status_change_modal.html.twig', [
            'error' => $error,
            'id' => $id,
            'doodle' => $doodle,
            'doodleStatuses' => $doodleStatuses,
        ]);

        return new JsonResponse($jsonData);
    }

    /**
     * @Route("admin/status_change_ajax", name="admin_status_change_ajax")
     */

    public function statusChangeAjax(Request $request, DoodleRepository $doodleRepository, DoodleStatusRepository $doodleStatusRepository): Response
    {
        $error = [];
        $jsonData['status'] = true;

        $id = $request->get('id');
        $statusId = $request->get('statusId');

        if( !is_numeric($id) )
            $error[] = 'Wrong input data';

        if( !is_numeric($statusId) )
            $error[] = 'Wrong input data';

        if(empty($error)){
            $doodle = $doodleRepository->findOne($id);
            $status = $doodleStatusRepository->findOne($statusId);
            $doodle->setStatus($status);
            $doodleRepository->save($doodle);
        }

        $jsonData['id'] = $id;
        $jsonData['statusId'] = $statusId;
        $jsonData['error'] = $error;

        return new JsonResponse($jsonData);
    }
}
