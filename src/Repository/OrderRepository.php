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
        $this->checkSupport($entity);

        return true;
    }
}
