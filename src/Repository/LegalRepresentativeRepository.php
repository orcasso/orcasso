<?php

namespace App\Repository;

use App\Entity\LegalRepresentative;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<LegalRepresentative>
 */
class LegalRepresentativeRepository extends AbstractRepository
{
    public const ENTITY_CLASS = LegalRepresentative::class;

    public function isRemovable(object $entity): bool
    {
        $this->checkSupport($entity);

        return true;
    }
}
