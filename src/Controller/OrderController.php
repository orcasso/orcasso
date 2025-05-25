<?php

namespace App\Controller;

use App\Entity\Configuration;
use App\Entity\Order;
use App\Repository\ConfigurationRepository;
use App\Repository\OrderRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/order')]
final class OrderController extends AbstractController
{
    public function __construct(protected OrderRepository $orderRepository, protected ConfigurationRepository $configuration)
    {
    }

    #[Route('/{identifier}/pay', name: 'order_pay', methods: ['GET'])]
    public function pay(#[MapEntity(mapping: ['identifier' => 'identifier'])] Order $order): Response
    {
        return $this->render('order/pay.html.twig', [
            'order' => $order,
            'payment_method_cheque_instruction' => $this->configuration->getValue(Configuration::ITEM_PAYMENT_METHOD_CHEQUE_INSTRUCTION),
            'payment_method_bank_transfer_iban' => $this->configuration->getValue(Configuration::ITEM_PAYMENT_METHOD_BANK_TRANSFER_IBAN),
            'payment_method_bank_transfer_bic' => $this->configuration->getValue(Configuration::ITEM_PAYMENT_METHOD_BANK_TRANSFER_BIC),
        ]);
    }
}
