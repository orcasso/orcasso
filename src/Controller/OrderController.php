<?php

namespace App\Controller;

use App\Entity\Configuration;
use App\Entity\Order;
use App\Entity\Payment;
use App\Repository\ConfigurationRepository;
use App\Repository\OrderRepository;
use App\Repository\PaymentRepository;
use App\Utils\HelloAsso;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/order')]
final class OrderController extends AbstractController
{
    public function __construct(protected OrderRepository $orderRepository, protected ConfigurationRepository $configuration)
    {
    }

    #[Route('/{identifier:order}/pay', name: 'order_pay', methods: ['GET'])]
    public function pay(Order $order): Response
    {
        return $this->render('order/pay.html.twig', [
            'order' => $order,
            'hello_asso' => ((bool) $this->configuration->getValue(Configuration::ITEM_HELLOASSO_CLIENT_ID)),
            'payment_method_cheque_instruction' => $this->configuration->getValue(Configuration::ITEM_PAYMENT_METHOD_CHEQUE_INSTRUCTION),
            'payment_method_bank_transfer_iban' => $this->configuration->getValue(Configuration::ITEM_PAYMENT_METHOD_BANK_TRANSFER_IBAN),
            'payment_method_bank_transfer_bic' => $this->configuration->getValue(Configuration::ITEM_PAYMENT_METHOD_BANK_TRANSFER_BIC),
        ]);
    }

    #[Route('/{identifier:order}/pay-hello-asso', name: 'order_pay_with_hello_asso', methods: ['GET', 'POST'])]
    public function payWithHelloAsso(Order $order, Request $request, HelloAsso $apiWrapper, PaymentRepository $paymentRepository): Response
    {
        if (null !== $error = $request->query->get('error')) {
            $this->addFlash('danger', $error);
            if (null !== $checkoutId = $request->query->get('checkoutIntentId', null)) {
                if (null !== $payment = $paymentRepository->findOneBy(['checkoutId' => $checkoutId])) {
                    $payment->setStatus(Payment::STATUS_CANCELLED);
                    $paymentRepository->update($payment);
                }
            }
        }

        $form = $this->createFormBuilder(['amount' => $order->getDueAmount()], ['translation_domain' => 'forms'])
            ->add('amount', MoneyType::class, [
                'label' => 'payment.label.amount',
                'attr' => ['readonly' => 'readonly'],
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                return $this->redirect($apiWrapper->intentCheckout($order, $form->get('amount')->getData()));
            } catch (\Throwable $throwable) {
                $this->addFlash('danger', 'error.order.pay_with_hello_asso_failed');
                $this->addFlash('danger', $throwable->getMessage());

                return $this->redirectToRoute('order_pay', ['identifier' => $order->getIdentifier()]);
            }
        }

        return $this->render('order/pay_with_hello_asso.html.twig', [
            'form' => $form->createView(),
            'order' => $order,
        ]);
    }

    #[Route('/{identifier:order}/{payment}', name: 'order_show_payment', methods: ['GET'])]
    public function showPayment(Order $order, Payment $payment, HelloAsso $helloAsso): Response
    {
        if ($order->getMember() !== $payment->getMember()) {
            throw $this->createNotFoundException('Payment not found');
        }
        if (Payment::STATUS_PENDING === $payment->getStatus()) {
            $helloAsso->getCheckoutStatus($payment);
        }

        return $this->render('order/show_payment.html.twig', [
            'order' => $order,
            'payment' => $payment,
        ]);
    }
}
