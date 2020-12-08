<?php

namespace App\Controller\Admin;

use App\Repository\DoodleRepository;
use App\Repository\DoodleStatusRepository;
use App\Security\Glide;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
