<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Table(name: 't_order')]
#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: MemberLog::class)]
class Order implements MemberLogObjectInterface
{
    use TimestampableEntity;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_VALIDATED = 'validated';

    // Available statuses (up to 10 characters)
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CANCELLED,
        self::STATUS_VALIDATED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'identifier', type: 'string', length: 20, unique: true)]
    protected string $identifier;

    #[ORM\Column(name: 'notes', type: 'text', options: ['default' => ''])]
    #[Gedmo\Versioned]
    protected string $notes = '';

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id', nullable: false)]
    #[Gedmo\Versioned]
    private Member $member;

    #[ORM\Column(name: 'total_amount', type: 'decimal', precision: 10, scale: 2)]
    protected string|int|float $totalAmount = 0;

    #[ORM\Column(name: 'paid_amount', type: 'decimal', precision: 10, scale: 2)]
    protected string|int|float $paidAmount = 0;

    #[ORM\Column(name: 'status', type: 'string', length: 10, options: ['default' => self::STATUS_PENDING])]
    #[Gedmo\Versioned]
    protected string $status = self::STATUS_PENDING;

    /**
     * @var Collection<OrderLine>
     */
    #[ORM\OneToMany(targetEntity: OrderLine::class, mappedBy: 'order', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection $lines;

    /**
     * @var Collection<Payment>
     */
    #[ORM\OneToMany(targetEntity: PaymentOrder::class, mappedBy: 'order')]
    private Collection $payments;

    #[ORM\OneToOne(targetEntity: OrderFormReply::class, mappedBy: 'order')]
    protected ?OrderFormReply $sourceFormReply = null;

    public function __construct()
    {
        $this->lines = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    #[ORM\PrePersist]
    public function setIdentifier(): static
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $this->identifier = 'CMD-'.date('Ymd-');
        while (\strlen($this->identifier) < 20) {
            $this->identifier .= $alphabet[random_int(0, \strlen($alphabet) - 1)];
        }

        return $this;
    }

    public function getNotes(): string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setMember(Member $member): static
    {
        $this->member = $member;

        return $this;
    }

    public function getTotalAmount(): float
    {
        return (float) $this->totalAmount;
    }

    public function getLinesTotalAmount(bool $ignoreAllowances): float
    {
        $totalAmount = 0;
        foreach ($this->lines as $line) {
            if ($ignoreAllowances && OrderLine::TYPE_ALLOWANCE === $line->getType()) {
                continue;
            }
            $totalAmount += $line->getAmount();
        }

        return $totalAmount;
    }

    public function setTotalAmount(float $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getPaidAmount(): float
    {
        return (float) $this->paidAmount;
    }

    public function setPaidAmount(float $paidAmount): static
    {
        $this->paidAmount = $paidAmount;

        return $this;
    }

    public function getDueAmount(): float
    {
        return $this->totalAmount - $this->getPaidAmount();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        if (!\in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException(\sprintf('Invalid status: %s', $status));
        }
        if (self::STATUS_CANCELLED === $status && !$this->canBeCancelled()) {
            throw new \RuntimeException('Cannot set status to cancelled');
        }
        $this->status = $status;

        return $this;
    }

    public function canBeRemoved(): bool
    {
        return $this->canBeCancelled() && !$this->sourceFormReply;
    }

    public function canBeCancelled(): bool
    {
        return 0 == $this->getPaidAmount();
    }

    /**
     * @return Collection<OrderLine>
     */
    public function getLines(): Collection
    {
        return $this->lines;
    }

    public function addLine(OrderLine $line): static
    {
        if ($line->getOrder() !== $this) {
            throw new \InvalidArgumentException('Invalid order line');
        }
        if (!$this->lines->contains($line)) {
            $this->lines->add($line);
        }

        return $this;
    }

    /**
     * @return Collection<PaymentOrder>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function getSourceFormReply(): ?OrderFormReply
    {
        return $this->sourceFormReply;
    }

    public function setSourceFormReply(?OrderFormReply $sourceFormReply): static
    {
        $this->sourceFormReply = $sourceFormReply;

        return $this;
    }

    public function getLogConcernedMember(): Member
    {
        return $this->member;
    }

    public function getFriendlyName(): string
    {
        return 'Commande '.$this->getIdentifier();
    }
}
