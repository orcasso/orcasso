<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\PaymentOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Payment>
 */
class PaymentRepository extends AbstractRepository
{
    public const ENTITY_CLASS = Payment::class;

    public function isRemovable(object $entity): bool
    {
        /* @var Payment $entity */
        $this->checkSupport($entity);

        return $entity->getOrders()->isEmpty();
    }

    protected function postUpdate(object $entity): void
    {
        /** @var Payment $entity */
        foreach ($entity->getOrders() as $paymentOrder) {
            $this->getEntityManager()->getRepository(Order::class)->recalculatePaidAmount($paymentOrder->getOrder());
        }
    }

    public function recalculateAmount(Payment $payment): void
    {
        $paymentAmount = (float) $this->getEntityManager()->getRepository(PaymentOrder::class)->createQueryBuilder('po')
            ->select('SUM(po.amount) as paid_amount')
            ->where('po.payment = :payment')
            ->setParameter('payment', $payment->getId())
            ->getQuery()->getSingleScalarResult();
        $payment->setAmount($paymentAmount);
        $this->update($payment);
    }
}
