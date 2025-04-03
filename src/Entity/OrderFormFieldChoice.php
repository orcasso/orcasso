<?php

namespace App\Entity;

use App\Repository\OrderFormFieldChoiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: OrderFormFieldChoiceRepository::class)]
#[ORM\UniqueConstraint(fields: ['field', 'activity'])]
class OrderFormFieldChoice
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: OrderFormField::class, inversedBy: 'choices')]
    #[ORM\JoinColumn(name: 'field_id', referencedColumnName: 'id', nullable: false)]
    private OrderFormField $field;

    #[ORM\ManyToOne(targetEntity: Activity::class)]
    #[ORM\JoinColumn(name: 'activity_id', referencedColumnName: 'id', nullable: true)]
    private ?Activity $activity = null;

    #[ORM\Column(name: 'activity_amount', type: 'decimal', precision: 10, scale: 2)]
    protected string|int|float $activityAmount = 0;

    #[ORM\Column(name: 'allowance_label', type: 'string', length: 255)]
    private string $allowanceLabel = '';

    #[ORM\Column(name: 'allowance_percentage', type: 'decimal', precision: 5, scale: 2, nullable: true)]
    protected string|int|float|null $allowancePercentage = null;

    public function __construct(OrderFormField $field)
    {
        $this->field = $field;
        $this->field->addChoice($this);
    }

    public function clone(OrderFormField $newField): static
    {
        $clone = clone $this;
        $clone->id = null;
        $clone->field = $newField;
        $newField->addChoice($clone);

        return $clone;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getField(): OrderFormField
    {
        return $this->field;
    }

    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    public function setActivity(?Activity $activity): static
    {
        if (OrderFormField::TYPE_ACTIVITY_CHOICE !== $this->getField()->getType()) {
            throw new \InvalidArgumentException('Invalid form field choice');
        }
        $this->activity = $activity;

        return $this;
    }

    public function getActivityAmount(): float
    {
        return $this->activityAmount;
    }

    public function setActivityAmount(float $activityAmount): static
    {
        if (OrderFormField::TYPE_ACTIVITY_CHOICE !== $this->getField()->getType()) {
            throw new \InvalidArgumentException('Invalid form field choice');
        }
        $this->activityAmount = $activityAmount;

        return $this;
    }

    public function getAllowanceLabel(): string
    {
        return $this->allowanceLabel;
    }

    public function setAllowanceLabel(string $allowanceLabel): static
    {
        if (OrderFormField::TYPE_ALLOWANCE_CHOICE !== $this->getField()->getType()) {
            throw new \InvalidArgumentException('Invalid form field choice');
        }
        $this->allowanceLabel = $allowanceLabel;

        return $this;
    }

    public function getAllowancePercentage(): float
    {
        return $this->allowancePercentage;
    }

    public function setAllowancePercentage(float $allowancePercentage): static
    {
        if (OrderFormField::TYPE_ALLOWANCE_CHOICE !== $this->getField()->getType()) {
            throw new \InvalidArgumentException('Invalid form field choice');
        }
        $this->allowancePercentage = $allowancePercentage;

        return $this;
    }
}
