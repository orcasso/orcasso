<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\User;
use App\Form\OrderLineType;
use App\Repository\ActivityRepository;
use App\Repository\OrderLineRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order/{order}/lines')]
#[IsGranted(User::ROLE_ADMIN_ORDER_EDIT)]
final class OrderLineController extends AbstractController
{
    public function __construct(protected OrderLineRepository $repository)
    {
    }

    #[Route('/new/{type}', name: 'admin_order_line_create', methods: ['GET', 'POST'])]
    public function create(Request $request, ActivityRepository $activityRepository, Order $order, string $type): Response
    {
        $line = match ($type) {
            OrderLine::TYPE_SIMPLE => OrderLine::createSimple($order),
            OrderLine::TYPE_ALLOWANCE => OrderLine::createAllowance($order)->setAllowanceBaseAmount($order->getLinesTotalAmount(ignoreAllowances: true)),
            OrderLine::TYPE_ACTIVITY_SUBSCRIPTION => OrderLine::createActivitySubscription($order, $activityRepository->findOneBy([])),
        };
        $form = $this->createForm(OrderLineType::class, $line);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($line);
            $this->addFlash('success', 'success.order_line.created');

            return $this->redirectToRoute('admin_order_edit', ['order' => $order->getId()]);
        }

        return $this->render('admin/order_line/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{line}', name: 'admin_order_line_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OrderLine $line): Response
    {
        $form = $this->createForm(OrderLineType::class, $line);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($line);
            $this->addFlash('success', 'success.order_line.updated');

            return $this->redirectToRoute('admin_order_edit', ['order' => $line->getOrder()->getId()]);
        }

        return $this->render('admin/order_line/edit.html.twig', [
            'line' => $line,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{line}/delete', name: 'admin_order_line_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, OrderLine $line): Response
    {
        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->remove($line);
            $this->addFlash('success', 'success.order_line.deleted');

            return $this->redirectToRoute('admin_order_edit', ['order' => $line->getOrder()->getId()]);
        }

        return $this->render('admin/order_line/delete.html.twig', [
            'line' => $line,
            'form' => $form->createView(),
        ]);
    }
}
