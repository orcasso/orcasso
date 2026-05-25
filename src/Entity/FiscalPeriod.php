<?php

namespace App\Entity;

use App\Repository\FiscalPeriodRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Table(name: 't_fiscal_period')]
#[ORM\Entity(repositoryClass: FiscalPeriodRepository::class)]
class FiscalPeriod
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, unique: true)]
    protected string $name = '';

    #[ORM\Column(name: 'is_current', type: 'boolean', options: ['default' => false])]
    protected bool $current = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function isCurrent(): bool
    {
        return $this->current;
    }

    public function setCurrent(bool $current): static
    {
        $this->current = $current;

        return $this;
    }
}
