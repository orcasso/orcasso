<?php

namespace App\Entity;

use App\Repository\OrderLineRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Table(name: 't_order_line')]
#[ORM\Entity(repositoryClass: OrderLineRepository::class)]
class OrderLine
{
    use TimestampableEntity;

    public const TYPE_SIMPLE = 'simple';
    public const TYPE_ACTIVITY_SUBSCRIPTION = 'activity_subscription';
    public const TYPE_ALLOWANCE = 'allowance';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'lines')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false)]
    private Order $order;

    #[ORM\Column(name: 'position', type: 'smallint')]
    private int $position = 0;

    #[ORM\Column(name: 'type', type: 'string', length: 255)]
    protected string $type = self::TYPE_SIMPLE;

    #[ORM\Column(name: 'label', type: 'text')]
    protected string $label = '';

    #[ORM\ManyToOne(targetEntity: Activity::class)]
    #[ORM\JoinColumn(name: 'subscribed_activity_id', referencedColumnName: 'id', nullable: true)]
    protected ?Activity $subscribedActivity = null;

    #[ORM\Column(name: 'allowance_percentage', type: 'decimal', precision: 5, scale: 2, nullable: true, options: ['default' => null])]
    protected string|int|float|null $allowancePercentage = null;

    #[ORM\Column(name: 'allowance_base_amount', type: 'decimal', precision: 10, scale: 2, nullable: true, options: ['default' => null])]
    protected string|int|float|null $allowanceBaseAmount = null;

    #[ORM\Column(name: 'amount', type: 'decimal', precision: 10, scale: 2)]
    protected string|int|float $amount = 0;

    protected function __construct(Order $order)
    {
        $this->order = $order;
        $this->position = 0;
        foreach ($order->getLines() as $line) {
            $this->position = max($this->position, $line->position + 1);
        }
        $this->order->addLine($this);
    }

    public static function createSimple(Order $order): static
    {
        return new static($order);
    }

    public static function createActivitySubscription(Order $order, Activity $activity): static
    {
        $line = new static($order);
        $line->type = static::TYPE_ACTIVITY_SUBSCRIPTION;
        $line->setSubscribedActivity($activity);

        return $line;
    }

    public static function createAllowance(Order $order): static
    {
        $line = new static($order);
        $line->type = static::TYPE_ALLOWANCE;

        return $line;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
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

    public function getType(): string
    {
        return $this->type;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getSubscribedActivity(): ?Activity
    {
        if (static::TYPE_ACTIVITY_SUBSCRIPTION !== $this->type) {
            throw new \InvalidArgumentException('Subscribed activity unexpected for this line.');
        }

        return $this->subscribedActivity;
    }

    public function setSubscribedActivity(?Activity $subscribedActivity): static
    {
        if (static::TYPE_ACTIVITY_SUBSCRIPTION !== $this->type) {
            throw new \InvalidArgumentException('Subscribed activity unexpected for this line.');
        }
        $this->subscribedActivity = $subscribedActivity;
        $this->label = $subscribedActivity->getName();

        return $this;
    }

    public function getAllowancePercentage(): ?float
    {
        if (static::TYPE_ALLOWANCE !== $this->type) {
            throw new \InvalidArgumentException('Allowance unexpected for this line.');
        }

        return $this->allowancePercentage;
    }

    public function setAllowancePercentage(?float $allowancePercentage): static
    {
        if (static::TYPE_ALLOWANCE !== $this->type) {
            throw new \InvalidArgumentException('Allowance unexpected for this line.');
        }
        $this->allowancePercentage = $allowancePercentage;
        if ($this->allowancePercentage) {
            $this->amount = -($this->allowanceBaseAmount ?? 0) * $this->allowancePercentage / 100;
        }

        return $this;
    }

    public function getAllowanceBaseAmount(): ?float
    {
        if (static::TYPE_ALLOWANCE !== $this->type) {
            throw new \InvalidArgumentException('Allowance unexpected for this line.');
        }

        return $this->allowanceBaseAmount;
    }

    public function setAllowanceBaseAmount(?float $allowanceBaseAmount): static
    {
        if (static::TYPE_ALLOWANCE !== $this->type) {
            throw new \InvalidArgumentException('Allowance unexpected for this line.');
        }
        $this->allowanceBaseAmount = $allowanceBaseAmount;
        if ($this->allowanceBaseAmount) {
            $this->amount = -$this->allowanceBaseAmount * ($this->allowancePercentage ?? 0) / 100;
        }

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}
