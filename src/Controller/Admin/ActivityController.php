<?php

namespace App\Controller\Admin;

use App\Entity\Activity;
use App\Form\ActivityType;
use App\Repository\ActivityRepository;
use App\Table\ActivityTableFactory;
use Kilik\TableBundle\Services\TableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/activities')]
final class ActivityController extends AbstractController
{
    public function __construct(protected ActivityRepository $repository)
    {
    }

    #[Route('/', name: 'admin_activity_list')]
    public function list(TableService $kilik, ActivityTableFactory $tableFactory): Response
    {
        $table = $tableFactory->getTable();

        return $this->render('admin/activity/list.html.twig', [
            'table' => $kilik->createFormView($table),
        ]);
    }

    #[Route('/_list', name: 'admin_activity_list_ajax')]
    public function _list(Request $request, TableService $kilik, ActivityTableFactory $tableFactory): Response
    {
        return $kilik->handleRequest($tableFactory->getTable(), $request);
    }

    #[Route('/new', name: 'admin_activity_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $activity = new Activity();
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($activity);
            $this->addFlash('success', 'success.activity.created');

            return $this->redirectToRoute('admin_activity_edit', ['activity' => $activity->getId()]);
        }

        return $this->render('admin/activity/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{activity}', name: 'admin_activity_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Activity $activity): Response
    {
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($activity);
            $this->addFlash('success', 'success.activity.updated');

            return $this->redirectToRoute('admin_activity_edit', ['activity' => $activity->getId()]);
        }

        return $this->render('admin/activity/edit.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{activity}/delete', name: 'admin_activity_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, Activity $activity): Response
    {
        if (!$this->repository->isRemovable($activity)) {
            $this->addFlash('warning', 'warning.activity.not_removable');

            return $this->redirectToRoute('admin_activity_edit', ['activity' => $activity->getId()]);
        }

        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->remove($activity);
            $this->addFlash('success', 'success.activity.deleted');

            return $this->redirectToRoute('admin_activity_list');
        }

        return $this->render('admin/activity/delete.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }
}
