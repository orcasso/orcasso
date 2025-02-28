<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Member>
 */
class MemberRepository extends AbstractRepository
{
    public const ENTITY_CLASS = Member::class;

    public function isRemovable(object $entity): bool
    {
        $this->checkSupport($entity);

        return true;
    }
}
