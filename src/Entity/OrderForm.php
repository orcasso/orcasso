<?php

namespace App\Entity;

use App\Repository\OrderFormRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: OrderFormRepository::class)]
class OrderForm
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'title', type: 'string', length: 255, unique: true)]
    private string $title = '';

    #[ORM\Column(name: 'description', type: 'text')]
    private string $description = '';

    #[ORM\Column(name: 'order_main_line_label', type: 'string', length: 255)]
    private string $orderMainLineLabel = '';

    #[ORM\Column(name: 'order_main_line_amount', type: 'decimal', precision: 10, scale: 2)]
    protected string|int|float $orderMainLineAmount = 0;

    #[ORM\Column(name: 'enabled', type: 'boolean')]
    protected bool $enabled = false;

    /**
     * @var Collection<OrderFormField>
     */
    #[ORM\OneToMany(targetEntity: OrderFormField::class, mappedBy: 'form', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection $fields;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }

    public function clone(): static
    {
        $clone = clone $this;
        $clone->id = null;
        $clone->fields = new ArrayCollection();
        foreach ($this->fields as $field) {
            $field->clone($clone);
        }

        return $clone;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getOrderMainLineLabel(): string
    {
        return $this->orderMainLineLabel;
    }

    public function setOrderMainLineLabel(string $orderMainLineLabel): self
    {
        $this->orderMainLineLabel = $orderMainLineLabel;

        return $this;
    }

    public function getOrderMainLineAmount(): float
    {
        return $this->orderMainLineAmount;
    }

    public function setOrderMainLineAmount(float $orderMainLineAmount): self
    {
        $this->orderMainLineAmount = $orderMainLineAmount;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return Collection<OrderFormField>
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function addField(OrderFormField $field): static
    {
        if ($field->getForm() !== $this) {
            throw new \InvalidArgumentException('Invalid field');
        }
        if (!$this->fields->contains($field)) {
            $this->fields->add($field);
        }

        return $this;
    }
}
