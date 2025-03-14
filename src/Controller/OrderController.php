<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Table\OrderTableFactory;
use Kilik\TableBundle\Services\TableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/orders')]
final class OrderController extends AbstractController
{
    public function __construct(protected OrderRepository $repository)
    {
    }

    #[Route('/', name: 'order_list')]
    public function list(TableService $kilik, OrderTableFactory $tableFactory): Response
    {
        $table = $tableFactory->getTable();

        return $this->render('order/list.html.twig', [
            'table' => $kilik->createFormView($table),
        ]);
    }

    #[Route('/_list', name: 'order_list_ajax')]
    public function _list(Request $request, TableService $kilik, OrderTableFactory $tableFactory): Response
    {
        return $kilik->handleRequest($tableFactory->getTable(), $request);
    }

    #[Route('/new', name: 'order_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($order);
            $this->addFlash('success', 'success.order.created');

            return $this->redirectToRoute('order_edit', ['order' => $order->getId()]);
        }

        return $this->render('order/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{order}', name: 'order_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Order $order): Response
    {
        return $this->render('order/edit.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{order}/edit', name: 'order_edit_header', methods: ['GET', 'POST'])]
    public function editHeader(Request $request, Order $order): Response
    {
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($order);
            $this->addFlash('success', 'success.order.updated');

            return $this->redirectToRoute('order_edit', ['order' => $order->getId()]);
        }

        return $this->render('order/edit_header.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{order}/delete', name: 'order_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, Order $order): Response
    {
        if (!$this->repository->isRemovable($order)) {
            $this->addFlash('warning', 'warning.order.not_removable');

            return $this->redirectToRoute('order_edit', ['order' => $order->getId()]);
        }

        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->remove($order);
            $this->addFlash('success', 'success.order.deleted');

            return $this->redirectToRoute('order_list');
        }

        return $this->render('order/delete.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }
}
