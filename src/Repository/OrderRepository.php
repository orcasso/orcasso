<?php

namespace App\Repository;

use App\Entity\Order;
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
        $qb->andWhere($qb->expr()->gt('o.totalAmount', 'o.paidAmount'))
            ->orderBy('m.firstName', 'ASC')
            ->addOrderBy('m.lastName', 'ASC')
            ->addOrderBy('o.id', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
