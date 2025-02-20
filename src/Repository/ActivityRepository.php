<?php

namespace App\Repository;

use App\Entity\Activity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Activity>
 */
class ActivityRepository extends AbstractRepository
{
    public const ENTITY_CLASS = Activity::class;

    public function isRemovable(object $entity): bool
    {
        return true;
    }
}
