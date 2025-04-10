<?php

namespace App\Repository;

use App\Entity\OrderForm;
use App\Entity\OrderFormReply;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<OrderForm>
 */
class OrderFormRepository extends AbstractRepository
{
    public const ENTITY_CLASS = OrderForm::class;

    public function isRemovable(object $entity): bool
    {
        /* @var OrderForm $entity */
        $this->checkSupport($entity);

        return 0 === $this->getEntityManager()->getRepository(OrderFormReply::class)->count(['form' => $entity->getId()]);
    }
}
