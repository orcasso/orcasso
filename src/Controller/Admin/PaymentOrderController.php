<?php

namespace App\Controller\Admin;

use App\Entity\Payment;
use App\Entity\PaymentOrder;
use App\Form\PaymentOrderType;
use App\Repository\PaymentOrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/payment/{payment}/orders')]
final class PaymentOrderController extends AbstractController
{
    public function __construct(protected PaymentOrderRepository $repository)
    {
    }

    #[Route('/new', name: 'admin_payment_order_create', methods: ['GET', 'POST'])]
    public function create(Request $request, Payment $payment): Response
    {
        $paymentOrder = new PaymentOrder($payment);
        $form = $this->createForm(PaymentOrderType::class, $paymentOrder);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($paymentOrder);
            $this->addFlash('success', 'success.payment.updated');

            return $this->redirectToRoute('admin_payment_edit', ['payment' => $payment->getId()]);
        }

        return $this->render('admin/payment_order/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{paymentOrder}', name: 'admin_payment_order_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PaymentOrder $paymentOrder): Response
    {
        $form = $this->createForm(PaymentOrderType::class, $paymentOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($paymentOrder);
            $this->addFlash('success', 'success.payment.updated');

            return $this->redirectToRoute('admin_payment_edit', ['payment' => $paymentOrder->getPayment()->getId()]);
        }

        return $this->render('admin/payment_order/edit.html.twig', [
            'order' => $paymentOrder,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{paymentOrder}/delete', name: 'admin_payment_order_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, PaymentOrder $paymentOrder): Response
    {
        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->remove($paymentOrder);
            $this->addFlash('success', 'success.payment.updated');

            return $this->redirectToRoute('admin_payment_edit', ['payment' => $paymentOrder->getPayment()->getId()]);
        }

        return $this->render('admin/payment_order/delete.html.twig', [
            'order' => $paymentOrder,
            'form' => $form->createView(),
        ]);
    }
}
