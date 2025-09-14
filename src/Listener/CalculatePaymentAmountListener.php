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
        $this->recalculateAmounts($args->getObject(), $args->getObjectManager());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->recalculateAmounts($args->getObject(), $args->getObjectManager());
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $this->recalculateAmounts($args->getObject(), $args->getObjectManager());
    }

    protected function recalculateAmounts(object $paymentOrder, EntityManagerInterface $em): void
    {
        if (!$paymentOrder instanceof PaymentOrder) {
            return;
        }
        $em->getRepository(Order::class)->recalculatePaidAmount($paymentOrder->getOrder());
        $em->getRepository(Payment::class)->recalculateAmount($paymentOrder->getPayment());
    }
}
