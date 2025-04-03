<?php

namespace App\Repository;

use App\Entity\OrderFormFieldChoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<OrderFormFieldChoice>
 */
class OrderFormFieldChoiceRepository extends AbstractRepository
{
    public const ENTITY_CLASS = OrderFormFieldChoice::class;

    public function isRemovable(object $entity): bool
    {
        $this->checkSupport($entity);

        return true;
    }
}
