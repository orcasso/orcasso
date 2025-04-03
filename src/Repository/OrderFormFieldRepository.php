<?php

namespace App\Repository;

use App\Entity\OrderFormField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<OrderFormField>
 */
class OrderFormFieldRepository extends AbstractRepository
{
    public const ENTITY_CLASS = OrderFormField::class;

    public function isRemovable(object $entity): bool
    {
        $this->checkSupport($entity);

        return true;
    }
}
