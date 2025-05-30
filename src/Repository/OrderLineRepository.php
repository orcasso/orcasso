<?php

namespace App\Repository;

use App\Entity\OrderLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<OrderLine>
 */
class OrderLineRepository extends AbstractRepository
{
    public const ENTITY_CLASS = OrderLine::class;

    public function isRemovable(object $entity): bool
    {
        $this->checkSupport($entity);

        return true;
    }
}
