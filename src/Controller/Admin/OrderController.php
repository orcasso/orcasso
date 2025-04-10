<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\User;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Table\OrderTableFactory;
use Kilik\TableBundle\Services\TableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/orders')]
#[IsGranted(User::ROLE_ADMIN_ORDER_EDIT)]
final class OrderController extends AbstractController
{
    public function __construct(protected OrderRepository $repository)
    {
    }

    #[Route('/', name: 'admin_order_list')]
    public function list(TableService $kilik, OrderTableFactory $tableFactory): Response
    {
        $table = $tableFactory->getTable();

        return $this->render('admin/order/list.html.twig', [
            'table' => $kilik->createFormView($table),
        ]);
    }

    #[Route('/_list', name: 'admin_order_list_ajax')]
    public function _list(Request $request, TableService $kilik, OrderTableFactory $tableFactory): Response
    {
        return $kilik->handleRequest($tableFactory->getTable(), $request);
    }

    #[Route('/new', name: 'admin_order_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($order);
            $this->addFlash('success', 'success.order.created');

            return $this->redirectToRoute('admin_order_edit', ['order' => $order->getId()]);
        }

        return $this->render('admin/order/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{order}', name: 'admin_order_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Order $order): Response
    {
        return $this->render('admin/order/edit.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{order}/edit', name: 'admin_order_edit_header', methods: ['GET', 'POST'])]
    public function editHeader(Request $request, Order $order): Response
    {
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($order);
            $this->addFlash('success', 'success.order.updated');

            return $this->redirectToRoute('admin_order_edit', ['order' => $order->getId()]);
        }

        return $this->render('admin/order/edit_header.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{order}/change-status/{status}', name: 'admin_order_change_status', methods: ['GET', 'POST'])]
    public function changeStatus(Request $request, Order $order, string $status)
    {
        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->setStatus($status);
            $this->repository->update($order);
            $this->addFlash('success', 'success.order.updated');

            return $this->redirectToRoute('admin_order_edit', ['order' => $order->getId()]);
        }

        return $this->render('admin/order/change_status.html.twig', [
            'order' => $order,
            'status' => $status,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{order}/delete', name: 'admin_order_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, Order $order): Response
    {
        if (!$this->repository->isRemovable($order)) {
            $this->addFlash('warning', 'warning.order.not_removable');

            return $this->redirectToRoute('admin_order_edit', ['order' => $order->getId()]);
        }

        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->remove($order);
            $this->addFlash('success', 'success.order.deleted');

            return $this->redirectToRoute('admin_order_list');
        }

        return $this->render('admin/order/delete.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }
}
