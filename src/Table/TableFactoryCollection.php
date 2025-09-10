<?php

namespace App\Table;

class TableFactoryCollection
{
    protected array $factories = [];

    /**
     * @param iterable<TableFactoryInterface> $factories
     */
    public function __construct(iterable $factories)
    {
        foreach ($factories as $factory) {
            if (isset($this->factories[$factory->getTableId()])) {
                throw new \InvalidArgumentException("Table factory {$factory->getTableId()} is already defined.");
            }
            $this->factories[$factory->getTableId()] = $factory;
        }
    }

    public function get(string $id): TableFactoryInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->getTableId() === $id) {
                return $factory;
            }
        }

        throw new \InvalidArgumentException("Unexpected factory id {$id}");
    }
}
