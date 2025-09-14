<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\PaymentOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends AbstractRepository
{
    public const ENTITY_CLASS = Order::class;

    public function isRemovable(object $entity): bool
    {
        /* @var Order $entity */
        $this->checkSupport($entity);

        return $entity->getPayments()->isEmpty();
    }

    /**
     * @return Order[]
     */
    public function findWaitingPayment(): array
    {
        $qb = $this->createQueryBuilder('o')->innerJoin('o.member', 'm');
        $qb
            ->andWhere($qb->expr()->gt('o.totalAmount', 'o.paidAmount'))
            ->andWhere($qb->expr()->neq('o.status', ':status_cancelled'))
            ->setParameter('status_cancelled', Order::STATUS_CANCELLED)
            ->orderBy('m.firstName', 'ASC')
            ->addOrderBy('m.lastName', 'ASC')
            ->addOrderBy('o.id', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Order[]
     */
    public function findActivesForMember(Member $member): array
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->andWhere($qb->expr()->eq('o.member', ':member'))
            ->andWhere($qb->expr()->neq('o.status', ':status_cancelled'))
            ->setParameter('status_cancelled', Order::STATUS_CANCELLED)
            ->setParameter('member', $member->getId())
            ->addOrderBy('o.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function recalculatePaidAmount(Order $order): void
    {
        $orderPaidAmount = (float) $this->getEntityManager()->getRepository(PaymentOrder::class)->createQueryBuilder('po')
            ->select('SUM(po.amount) as paid_amount')
            ->innerJoin('po.payment', 'payment')
            ->where('po.order =  :order')
            ->andWhere('payment.status <> :cancelled')
            ->setParameter('order', $order->getId())
            ->setParameter('cancelled', Payment::STATUS_CANCELLED)
            ->getQuery()->getSingleScalarResult();
        $order->setPaidAmount($orderPaidAmount);
        $this->update($order);
    }
}
