<?php

namespace App\Repository;

use App\Entity\Configuration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Configuration>
 */
class ConfigurationRepository extends AbstractRepository
{
    public const ENTITY_CLASS = Configuration::class;

    public function isRemovable(object $entity): bool
    {
        $this->checkSupport($entity);

        return true;
    }

    public function get(string $item): Configuration
    {
        return $this->findOneBy(['item' => $item]) ?? new Configuration($item);
    }

    public function getValue(string $item): string
    {
        return $this->get($item)->getValue();
    }
}
