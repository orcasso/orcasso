<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

abstract class AbstractRepository extends ServiceEntityRepository
{
    public const ENTITY_CLASS = null;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, static::ENTITY_CLASS);
    }

    final public function supports(object $entity): bool
    {
        return is_a($entity, static::ENTITY_CLASS);
    }

    protected function preUpdate(object $entity): void
    {
        // Extends this method if necessary.
    }

    final public function update(object $entity, bool $andFlush = true): void
    {
        $this->checkSupport($entity);

        $this->preUpdate($entity);

        $this->getEntityManager()->persist($entity);

        if ($andFlush) {
            $this->getEntityManager()->flush();
        }

        $this->postUpdate($entity);
    }

    protected function postUpdate(object $entity): void
    {
        // Extends this method if necessary.
    }

    public function isRemovable(object $entity): bool
    {
        $this->checkSupport($entity);

        return false;
    }

    final public function remove(object $entity, bool $andFlush = true): object
    {
        $this->checkSupport($entity);

        if (!$this->isRemovable($entity)) {
            throw new \LogicException();
        }

        $this->getEntityManager()->remove($entity);
        if ($andFlush) {
            $this->getEntityManager()->flush();
        }

        return $entity;
    }

    final protected function checkSupport(object $entity): void
    {
        if (!$this->supports($entity)) {
            $objectType = $entity::class;
            $expected = static::ENTITY_CLASS;

            throw new \InvalidArgumentException("Expected {$expected} got {$objectType}");
        }
    }
}
