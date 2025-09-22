<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\PaymentOrder;
use App\Entity\User;
use App\Form\PaymentType;
use App\Repository\PaymentOrderRepository;
use App\Repository\PaymentRepository;
use App\Table\PaymentTableFactory;
use App\Utils\HelloAsso;
use Kilik\TableBundle\Services\TableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/payments')]
#[IsGranted(User::ROLE_ADMIN_PAYMENT_EDIT)]
final class PaymentController extends AbstractController
{
    public function __construct(protected PaymentRepository $repository)
    {
    }

    #[Route('/', name: 'admin_payment_list')]
    public function list(TableService $kilik, PaymentTableFactory $tableFactory): Response
    {
        $table = $tableFactory->getTable();

        return $this->render('admin/payment/list.html.twig', [
            'table' => $kilik->createFormView($table),
        ]);
    }

    #[Route('/_list', name: 'admin_payment_list_ajax')]
    public function _list(Request $request, TableService $kilik, PaymentTableFactory $tableFactory): Response
    {
        return $kilik->handleRequest($tableFactory->getTable(), $request);
    }

    #[Route('/new', name: 'admin_payment_create', methods: ['GET', 'POST'])]
    #[Route('/new/{order}', name: 'admin_payment_create_for_order', methods: ['GET', 'POST'])]
    public function create(Request $request, ?Order $order, PaymentOrderRepository $paymentOrderRepository): Response
    {
        $payment = new Payment();
        if ($order) {
            $payment->setMember($order->getMember());
            $payment->getOrders()->add((new PaymentOrder($payment, $order))->setAmount($order->getDueAmount()));
        }
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($payment);
            $this->addFlash('success', 'success.payment.created');

            return $this->redirectToRoute('admin_payment_edit', ['payment' => $payment->getId()]);
        }

        return $this->render('admin/payment/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{payment}', name: 'admin_payment_edit', methods: ['GET', 'POST'])]
    public function edit(Payment $payment, HelloAsso $helloAsso): Response
    {
        if (Payment::STATUS_PENDING === $payment->getStatus()) {
            $helloAsso->getCheckoutStatus($payment);
        }

        return $this->render('admin/payment/edit.html.twig', [
            'payment' => $payment,
        ]);
    }

    #[Route('/{payment}/edit', name: 'admin_payment_edit_header', methods: ['GET', 'POST'])]
    public function editHeader(Request $request, Payment $payment): Response
    {
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($payment);
            $this->addFlash('success', 'success.payment.updated');

            return $this->redirectToRoute('admin_payment_edit', ['payment' => $payment->getId()]);
        }

        return $this->render('admin/payment/edit_header.html.twig', [
            'payment' => $payment,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{payment}/delete', name: 'admin_payment_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, Payment $payment): Response
    {
        if (!$this->repository->isRemovable($payment)) {
            $this->addFlash('warning', 'warning.payment.not_removable');

            return $this->redirectToRoute('admin_payment_edit', ['payment' => $payment->getId()]);
        }

        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->remove($payment);
            $this->addFlash('success', 'success.payment.deleted');

            return $this->redirectToRoute('admin_payment_list');
        }

        return $this->render('admin/payment/delete.html.twig', [
            'payment' => $payment,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{payment}/change-status/{status}', name: 'admin_payment_change_status', methods: ['GET', 'POST'])]
    public function changeStatus(Request $request, Payment $payment, string $status): \Symfony\Component\HttpFoundation\RedirectResponse|Response
    {
        $form = $this->createFormBuilder($payment);
        if (Payment::STATUS_VALIDATED === $status) {
            $form->add('receivedAt', DateType::class, [
                'label' => 'payment.label.received_at',
                'widget' => 'single_text',
                'required' => false,
                'translation_domain' => 'forms',
                'data' => date_create_immutable(),
            ]);
        }

        $form = $form->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $payment->setStatus($status);
            if (Payment::STATUS_VALIDATED !== $status) {
                $payment->setReceivedAt(null);
            }
            $this->repository->update($payment);
            $this->addFlash('success', 'success.payment.updated');

            return $this->redirectToRoute('admin_payment_edit', ['payment' => $payment->getId()]);
        }

        return $this->render('admin/payment/change_status.html.twig', [
            'payment' => $payment,
            'status' => $status,
            'form' => $form->createView(),
        ]);
    }
}
