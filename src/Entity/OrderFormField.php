<?php

namespace App\Entity;

use App\Repository\OrderFormFieldRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: OrderFormFieldRepository::class)]
#[ORM\UniqueConstraint(fields: ['form', 'question'])]
class OrderFormField
{
    use TimestampableEntity;

    public const TYPE_ACTIVITY_CHOICE = 'activity_choice';
    public const TYPE_ALLOWANCE_CHOICE = 'allowance_choice';
    public const TYPES = [
        self::TYPE_ACTIVITY_CHOICE,
        self::TYPE_ALLOWANCE_CHOICE,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: OrderForm::class, inversedBy: 'fields')]
    #[ORM\JoinColumn(name: 'form_id', referencedColumnName: 'id', nullable: false)]
    private OrderForm $form;

    #[ORM\Column(name: 'position', type: 'smallint')]
    private int $position = 0;

    #[ORM\Column(name: 'question', type: 'string', length: 255)]
    private string $question = '';

    #[ORM\Column(name: 'type', type: 'string', length: 20)]
    private string $type = self::TYPE_ALLOWANCE_CHOICE;

    /**
     * @var Collection<OrderFormFieldChoice>
     */
    #[ORM\OneToMany(targetEntity: OrderFormFieldChoice::class, mappedBy: 'field', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection $choices;

    #[ORM\Column(name: 'required', type: 'boolean')]
    protected bool $required = true;

    public function __construct(OrderForm $form)
    {
        $this->form = $form;
        $this->position = 0;
        foreach ($form->getFields() as $field) {
            $this->position = max($this->position, $field->position + 1);
        }
        $this->form->addField($this);
        $this->choices = new ArrayCollection();
    }

    public function clone(OrderForm $newForm): static
    {
        $clone = clone $this;
        $clone->id = null;
        $clone->form = $newForm;
        $newForm->addField($clone);
        $clone->choices = new ArrayCollection();
        foreach ($this->choices as $choice) {
            $choice->clone($clone);
        }

        return $clone;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getForm(): OrderForm
    {
        return $this->form;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        if ($this->getId()) {
            throw new \LogicException('Unable to change existing field type');
        }
        if (!\in_array($type, self::TYPES, true)) {
            throw new \InvalidArgumentException("Invalid type: {$type}");
        }
        $this->type = $type;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): static
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return Collection<OrderFormFieldChoice>
     */
    public function getChoices(): Collection
    {
        return $this->choices;
    }

    public function addChoice(OrderFormFieldChoice $choice): static
    {
        if ($choice->getField() !== $this) {
            throw new \InvalidArgumentException('Invalid choice');
        }
        if (!$this->choices->contains($choice)) {
            $this->choices->add($choice);
        }

        return $this;
    }
}
