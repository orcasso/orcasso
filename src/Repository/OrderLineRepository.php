<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\OrderLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderLineRepository extends AbstractRepository
{
    public const ENTITY_CLASS = OrderLine::class;

    public function isRemovable(object $entity): bool
    {
        return true;
    }
}
