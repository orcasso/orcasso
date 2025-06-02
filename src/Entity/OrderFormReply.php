<?php

namespace App\Entity;

use App\Model\MemberData;
use App\Repository\OrderFormReplyRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Table(name: 't_order_form_reply')]
#[ORM\Entity(repositoryClass: OrderFormReplyRepository::class)]
class OrderFormReply
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: OrderForm::class)]
    #[ORM\JoinColumn(name: 'form_id', referencedColumnName: 'id', nullable: false)]
    private OrderForm $form;

    #[ORM\Column(name: 'member_data', type: 'json')]
    public array $memberRawData = [];

    public ?MemberData $memberData = null;

    #[ORM\Column(name: 'notes', type: 'text', options: ['default' => ''])]
    public string $notes = '';

    #[ORM\OneToOne(targetEntity: Order::class, inversedBy: 'sourceFormReply')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', unique: true, nullable: true, onDelete: 'SET NULL')]
    protected ?Order $order = null;

    #[ORM\Column(name: 'field_values', type: 'json')]
    public array $fieldValues = [];

    public function __construct(OrderForm $form)
    {
        $this->form = $form;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getForm(): OrderForm
    {
        return $this->form;
    }

    public function getMemberData(): MemberData
    {
        if (null === $this->memberData) {
            $this->memberData = MemberData::denormalize($this->memberRawData);
        }

        return $this->memberData;
    }

    public function applyMemberData(): static
    {
        $this->memberRawData = $this->memberData->normalize();

        return $this;
    }

    public function getFieldValues(): array
    {
        return $this->fieldValues;
    }

    public function getFieldValue(OrderFormField $field): ?string
    {
        return $this->fieldValues[$field->getQuestion()] ?? null;
    }

    public function getFieldChoice(OrderFormField $field): ?OrderFormFieldChoice
    {
        $value = $this->getFieldValue($field);
        if (empty($value)) {
            return null;
        }
        foreach ($field->getChoices() as $choice) {
            if ($value === $choice->getActivity()?->getName()) {
                return $choice;
            }
            if ($value === $choice->getAllowanceLabel()) {
                return $choice;
            }
        }

        return null;
    }

    public function setFieldValues(array $fieldValues): static
    {
        $this->fieldValues = $fieldValues;

        return $this;
    }

    public function setFieldValue(string $fieldQuestion, string $fieldValue): static
    {
        $this->fieldValues[$fieldQuestion] = $fieldValue;

        return $this;
    }

    public function calculateTotalAmount(): float
    {
        $amount = $this->form->getOrderMainLineAmount();
        foreach ($this->form->getFields() as $field) {
            if (null === $repliedChoice = $this->getFieldChoice($field)) {
                continue;
            }

            if ($repliedChoice->getActivityAmount()) {
                $amount += $repliedChoice->getActivityAmount();
            } elseif ($repliedChoice->getAllowancePercentage()) {
                $amount -= round($amount * $repliedChoice->getAllowancePercentage() / 100);
            }
        }

        return $amount;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;
        $order?->setSourceFormReply($this);

        return $this;
    }
}
