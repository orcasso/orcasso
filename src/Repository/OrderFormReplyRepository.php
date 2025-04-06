<?php

namespace App\Repository;

use App\Entity\OrderFormReply;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<OrderFormReply>
 */
class OrderFormReplyRepository extends AbstractRepository
{
    public const ENTITY_CLASS = OrderFormReply::class;

    public function isRemovable(object $entity): bool
    {
        /* @var OrderFormReply $entity */
        $this->checkSupport($entity);

        return true;
    }
}
