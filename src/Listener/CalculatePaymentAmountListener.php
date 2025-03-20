<?php

namespace App\Listener;

use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\PaymentOrder;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postRemove)]
class CalculatePaymentAmountListener
{
    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->recalculateAmount($args->getObject(), $args->getObjectManager());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->recalculateAmount($args->getObject(), $args->getObjectManager());
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $this->recalculateAmount($args->getObject(), $args->getObjectManager());
    }

    protected function recalculateAmount(object $paymentOrder, EntityManagerInterface $em): void
    {
        if (!$paymentOrder instanceof PaymentOrder) {
            return;
        }
        $paymentOrderRepository = $em->getRepository(PaymentOrder::class);
        $order = $paymentOrder->getOrder();
        $payment = $paymentOrder->getPayment();

        $orderRepository = $em->getRepository(Order::class);
        $orderPaidAmount = (float) $paymentOrderRepository->createQueryBuilder('po')
            ->select('SUM(po.amount) as paid_amount')
            ->where('po.order =  :order')
            ->setParameter('order', $order->getId())
            ->getQuery()->getSingleScalarResult();
        $order->setPaidAmount($orderPaidAmount);
        $orderRepository->update($order);

        $paymentRepository = $em->getRepository(Payment::class);
        $paymentAmount = (float) $paymentOrderRepository->createQueryBuilder('po')
                ->select('SUM(po.amount) as paid_amount')
                ->where('po.payment = :payment')
                ->setParameter('payment', $payment->getId())
                ->getQuery()->getSingleScalarResult();
        $payment->setAmount($paymentAmount);
        $paymentRepository->update($payment);
    }
}
