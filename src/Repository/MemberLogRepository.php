<?php

namespace App\Repository;

use App\Entity\MemberLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<MemberLog>
 */
class MemberLogRepository extends AbstractRepository
{
    public const ENTITY_CLASS = MemberLog::class;
}
