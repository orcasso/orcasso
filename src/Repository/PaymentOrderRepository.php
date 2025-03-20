<?php

namespace App\Repository;

use App\Entity\PaymentOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<PaymentOrder>
 */
class PaymentOrderRepository extends AbstractRepository
{
    public const ENTITY_CLASS = PaymentOrder::class;

    public function isRemovable(object $entity): bool
    {
        $this->checkSupport($entity);

        return true;
    }
}
