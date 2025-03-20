<?php

namespace App\Repository;

use App\Entity\Payment;
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
}
