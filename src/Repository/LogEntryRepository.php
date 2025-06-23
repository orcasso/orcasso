<?php

namespace App\Repository;

use App\Entity\LogEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<LogEntry>
 */
class LogEntryRepository extends AbstractRepository
{
    public const ENTITY_CLASS = LogEntry::class;
}
