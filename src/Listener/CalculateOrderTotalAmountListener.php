<?php

namespace App\Listener;

use App\Entity\Order;
use App\Entity\OrderLine;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postRemove)]
class CalculateOrderTotalAmountListener
{
    public function postPersist(PostPersistEventArgs $args): void
    {
        $order = $args->getObject();
        if ($order instanceof OrderLine) {
            $order = $order->getOrder();
        }

        if (!$order instanceof Order) {
            return;
        }

        $this->recalculateAmount($order, $args->getObjectManager());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $order = $args->getObject();
        if ($order instanceof OrderLine) {
            $order = $order->getOrder();
        }

        if (!$order instanceof Order) {
            return;
        }

        $this->recalculateAmount($order, $args->getObjectManager());
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $line = $args->getObject();
        if (!$line instanceof OrderLine) {
            return;
        }
        // order removal
        if (!$line->getOrder()->getId()) {
            return;
        }

        $this->recalculateAmount($line->getOrder(), $args->getObjectManager());
    }

    protected function recalculateAmount(Order $order, EntityManager $em): void
    {
        $repository = $em->getRepository(Order::class);
        $totalAmount = $order->getLinesTotalAmount(ignoreAllowances: false);
        $order->setTotalAmount(round($totalAmount, 2));
        $repository->update($order);
    }
}
