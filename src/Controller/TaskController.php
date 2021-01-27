<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\TaskStatus;
use App\Repository\TaskRepository;
use App\Repository\TaskStatusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TaskController extends AbstractController
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/{_locale<%app.supported_locales%>}/task", name="task")
     */
    public function index(
        TaskStatusRepository $taskStatusRepository,
        TaskRepository $taskRepository
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $statuses = $taskStatusRepository->getUserStatuses($user);

        return $this->render('task/index.html.twig', [
            'statuses' => $statuses,
            'taskRepository' => $taskRepository,
        ]);
    }

    /**
     * @Route("/task/status_change_modal_view", name="task_status_change_modal_view")
     * @throws \Exception
     */

    public function statusChangeModalView(Request $request, TaskRepository $taskRepository,
                                          TaskStatusRepository $taskStatusRepository)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $error = [];
        $jsonData['status'] = true;

        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'status' => false,
                    'message' => 'Error! Not Xml Http Request'
                ],
                400);
        }

        $id = $request->get('id');

        if (!is_numeric($id)) {
            $jsonData['status'] = false;
            $error[] = 'Wrong id';
        } else {
            $task = $taskRepository->findOne($id);
        }

        $taskStatuses = $taskStatusRepository->getUserStatuses($user);

        $jsonData['content'] = $this->renderView('task/status_change_modal.html.twig', [
            'error' => $error,
            'id' => $id,
            'task' => $task,
            'taskStatuses' => $taskStatuses,
        ]);

        return new JsonResponse($jsonData);
    }

    /**
     * @Route("/api/task/{id<\d+>}", name="task_param_change_ajax", methods={"PATCH"})
     */

    public function paramChangeApi(
        int $id,
        Request $request,
        TaskRepository $taskRepository,
        TaskStatusRepository $taskStatusRepository
    ): JsonResponse
    {
        $error = [];
        $response = new JsonResponse();

        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $error[] = $this->translator->trans("You can't edit this status");
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $statusId = $request->get('statusId');

        if (!is_numeric($id)) {
            $error[] = $this->translator->trans("Wrong id");
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            $task = $taskRepository->findOne($id);
        }

        if (!is_numeric($statusId)) {
            $error[] = $this->translator->trans("Wrong status id");
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        if (empty($error)) {
            $status = $taskStatusRepository->findOne($statusId);
            $task->setStatus($status);
            $entityManager->persist($task);
            $entityManager->flush();
        }

        return $response->setData(['error' => $error]);
    }

    /**
     * @Route("/task/manage_modal_view", name="task_manage_modal_view")
     * @throws \Exception
     */

    public function taskManageModalView(Request $request, TaskRepository $taskRepository)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $error = [];
        $jsonData['status'] = true;

        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'status' => false,
                    'message' => 'Error! Not Xml Http Request'
                ],
                400);
        }

        $id = $request->get('id');
        $user = $this->getUser();

        if (is_numeric($id)) {
            $task = $taskRepository->findOne($id);
        } else {
            $task = new Task();
            if ($user != $task->getUser()) {
                $error[] = $this->translator->trans("You can't edit this task");
            }
        }

        $jsonData['content'] = $this->renderView('task/manage_modal.html.twig', [
            'error' => $error,
            'id' => $id,
            'task' => $task,
        ]);

        return new JsonResponse($jsonData);
    }

    /**
     * @Route("/api/task/{id<\d+>}", name="task_manage_ajax",
     *     methods={"PUT|POST"}, defaults={"id": null})
     */

    public function taskManageApi(
        ?int $id,
        Request $request,
        TaskRepository $taskRepository,
        TaskStatusRepository $taskStatusRepository
    ): JsonResponse
    {
        $error = [];
        $response = new JsonResponse();

        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $error[] = $this->translator->trans("You can't edit this status");
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $title = $request->get('title');

        if (strlen($title) <= 0) {
            $error[] = $this->translator->trans('Please write title');
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        if (!is_numeric($id)) {
            $task = new Task();
            $task->setStatus($taskStatusRepository->findOneBy(['id' => TaskStatus::STATUS_TO_DO]));
        } else {
            $task = $taskRepository->findOne($id);
            if ($user != $task->getUser()) {
                $error[] = $this->translator->trans("You can't edit this task");
                $response->setStatusCode(Response::HTTP_FORBIDDEN);
            }
        }

        if (empty($error)) {
            $task->setTitle($title);
            $task->setUser($user);
            $entityManager->persist($task);
            $entityManager->flush();
        }

        return $response->setData(['error' => $error]);
    }

    /**
     * @Route("/task/manage_board_modal_view", name="task_board_manage_modal_view")
     * @throws \Exception
     */

    public function taskBoardManageModalView(Request $request, TaskStatusRepository $taskStatusRepository)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $error = [];
        $jsonData['status'] = true;

        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Error! Not Xml Http Request'
            ],
                400);
        }

        $id = $request->get('id');

        if (is_numeric($id)) {
            $taskStatus = $taskStatusRepository->findOne($id);
        } else {
            $taskStatus = new TaskStatus();
        }

        $jsonData['content'] = $this->renderView('task/manage_board_modal.html.twig', [
            'error' => $error,
            'id' => $id,
            'status' => $taskStatus,
        ]);

        return new JsonResponse($jsonData);
    }

    /**
     * @Route("/api/task/board/{id<\d+>}", name="task_board_manage_ajax",
     *     methods={"PUT|POST"}, defaults={"id": null})
     */

    public function taskBoardManageApi(
        ?int $id,
        Request $request,
        TaskStatusRepository $taskStatusRepository
    ): JsonResponse
    {
        $error = [];
        $response = new JsonResponse();

        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $error[] = $this->translator->trans("You can't edit this status");
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $name = $request->get('name');

        if (strlen($name) <= 0) {
            $error[] = $this->translator->trans('Please write title');
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        if (!is_numeric($id)) {
            $taskStatus = new TaskStatus();
            $taskStatus->setUser($user);
        } else {
            $taskStatus = $taskStatusRepository->findOne($id);
            if ($user != $taskStatus->getUser()) {
                $error[] = $this->translator->trans("You can't edit this status");
                $response->setStatusCode(Response::HTTP_FORBIDDEN);
            }
        }

        if (empty($error)) {
            $taskStatus->setName($name);
            $entityManager->persist($taskStatus);
            $entityManager->flush();
        }

        return $response->setData(['error' => $error]);
    }

    /**
     * @Route("/task/delete_modal_view", name="task_delete_modal_view")
     * @throws \Exception
     */

    public function taskDeleteModalView(Request $request, TaskRepository $taskRepository)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $error = [];
        $jsonData['status'] = true;

        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'status' => false,
                    'message' => 'Error! Not Xml Http Request'
                ],
                400);
        }

        $id = $request->get('id');

        if (!is_numeric($id)) {
            $error[] = $this->translator->trans("Wrong id");
        } else {
            $task = $taskRepository->findOne($id);
        }

        $jsonData['content'] = $this->renderView('task/delete_modal.html.twig', [
            'error' => $error,
            'id' => $id,
            'task' => $task,
        ]);

        return new JsonResponse($jsonData);
    }

    /**
     * @Route("/api/task/{id<\d+>}", name="task_delete_ajax", methods={"DELETE"})
     */

    public function taskDeleteApi(
        int $id,
        TaskRepository $taskRepository
    ): JsonResponse
    {
        $error = [];
        $response = new JsonResponse();

        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $error[] = $this->translator->trans("You can't edit this status");
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if (!is_numeric($id)) {
            $error[] = $this->translator->trans("Wrong id");
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            $task = $taskRepository->findOne($id);
            if ($user != $task->getUser()) {
                $error[] = $this->translator->trans("You can't edit this task");
                $response->setStatusCode(Response::HTTP_FORBIDDEN);
            }
        }

        if (empty($error)) {
            $entityManager->remove($task);
            $entityManager->flush();
        }

        return $response->setData(['error' => $error]);
    }

    /**
     * @Route("/task/delete_board_modal_view", name="task_board_delete_modal_view")
     * @throws \Exception
     */

    public function taskBoardDeleteModalView(Request $request, TaskStatusRepository $taskStatusRepository)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $error = [];
        $jsonData['status'] = true;

        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'status' => false,
                    'message' => 'Error! Not Xml Http Request'
                ],
                400);
        }

        $id = $request->get('id');

        if (!is_numeric($id)) {
            $error[] = $this->translator->trans("Wrong id");
        } else {
            $taskStatus = $taskStatusRepository->findOne($id);
        }

        $jsonData['content'] = $this->renderView('task/delete_board_modal.html.twig', [
            'error' => $error,
            'id' => $id,
            'taskStatus' => $taskStatus,
        ]);

        return new JsonResponse($jsonData);
    }

    /**
     * @Route("/api/task/board/{id<\d+>}", name="task_board_ajax", methods={"DELETE"})
     */

    public function taskBoardDeleteApi(
        int $id,
        Request $request,
        TaskStatusRepository $taskStatusRepository
    ): JsonResponse
    {
        $error = [];
        $response = new JsonResponse();

        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $error[] = $this->translator->trans("You can't edit this status");
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if (!is_numeric($id)) {
            $error[] = $this->translator->trans("Wrong id");
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            $taskStatus = $taskStatusRepository->findOne($id);
            if ($user != $taskStatus->getUser()) {
                $error[] = $this->translator->trans("You can't edit this status");
                $response->setStatusCode(Response::HTTP_FORBIDDEN);
            }

            if (count($taskStatus->getTasks()) > 0) {
                $error[] = $this->translator->trans("You can't delete status with tasks");
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            }
        }

        if (empty($error)) {
            $entityManager->remove($taskStatus);
            $entityManager->flush();
        }

        return $response->setData(['error' => $error]);
    }
}
