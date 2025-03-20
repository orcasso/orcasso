<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\PaymentOrder;
use App\Form\PaymentType;
use App\Repository\PaymentOrderRepository;
use App\Repository\PaymentRepository;
use App\Table\PaymentTableFactory;
use Kilik\TableBundle\Services\TableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/payments')]
final class PaymentController extends AbstractController
{
    public function __construct(protected PaymentRepository $repository)
    {
    }

    #[Route('/', name: 'payment_list')]
    public function list(TableService $kilik, PaymentTableFactory $tableFactory): Response
    {
        $table = $tableFactory->getTable();

        return $this->render('payment/list.html.twig', [
            'table' => $kilik->createFormView($table),
        ]);
    }

    #[Route('/_list', name: 'payment_list_ajax')]
    public function _list(Request $request, TableService $kilik, PaymentTableFactory $tableFactory): Response
    {
        return $kilik->handleRequest($tableFactory->getTable(), $request);
    }

    #[Route('/new', name: 'payment_create', methods: ['GET', 'POST'])]
    #[Route('/new/{order}', name: 'payment_create_for_order', methods: ['GET', 'POST'])]
    public function create(Request $request, ?Order $order, PaymentOrderRepository $paymentOrderRepository): Response
    {
        $payment = new Payment();
        if ($order) {
            $payment->setMember($order->getMember());
        }
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($payment);
            if ($order) {
                $paymentOrderRepository->update((new PaymentOrder($payment, $order))->setAmount($order->getDueAmount()));
            }
            $this->addFlash('success', 'success.payment.created');

            return $this->redirectToRoute('payment_edit', ['payment' => $payment->getId()]);
        }

        return $this->render('payment/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{payment}', name: 'payment_edit', methods: ['GET', 'POST'])]
    public function edit(Payment $payment): Response
    {
        return $this->render('payment/edit.html.twig', [
            'payment' => $payment,
        ]);
    }

    #[Route('/{payment}/edit', name: 'payment_edit_header', methods: ['GET', 'POST'])]
    public function editHeader(Request $request, Payment $payment): Response
    {
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($payment);
            $this->addFlash('success', 'success.payment.updated');

            return $this->redirectToRoute('payment_edit', ['payment' => $payment->getId()]);
        }

        return $this->render('payment/edit_header.html.twig', [
            'payment' => $payment,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{payment}/delete', name: 'payment_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, Payment $payment): Response
    {
        if (!$this->repository->isRemovable($payment)) {
            $this->addFlash('warning', 'warning.payment.not_removable');

            return $this->redirectToRoute('payment_edit', ['payment' => $payment->getId()]);
        }

        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->remove($payment);
            $this->addFlash('success', 'success.payment.deleted');

            return $this->redirectToRoute('payment_list');
        }

        return $this->render('payment/delete.html.twig', [
            'payment' => $payment,
            'form' => $form->createView(),
        ]);
    }
}
