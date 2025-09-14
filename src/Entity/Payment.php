<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Table(name: 't_payment')]
#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[Gedmo\Loggable(logEntryClass: MemberLog::class)]
class Payment implements MemberLogObjectInterface
{
    use TimestampableEntity;

    // Up to 20 characters
    public const METHOD_CB = 'cb';
    public const METHOD_CB_RECURRENT = 'cb_recurrent'; // @todo verification ?
    public const METHOD_BANK_TRANSFER = 'bank_transfer';
    public const METHOD_CHEQUE = 'cheque';
    public const METHOD_CASH = 'cash';
    public const METHOD_HELLO_ASSO = 'hello_asso';
    public const METHOD_TOP_DEPART = 'top_depart';
    public const METHOD_HOLIDAY_VOUCHER = 'holiday_voucher';

    public const METHODS = [
        self::METHOD_CB,
        self::METHOD_CB_RECURRENT,
        self::METHOD_BANK_TRANSFER,
        self::METHOD_CHEQUE,
        self::METHOD_CASH,
        self::METHOD_HELLO_ASSO,
        self::METHOD_TOP_DEPART,
        self::METHOD_HOLIDAY_VOUCHER,
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_VALIDATED = 'validated';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CANCELLED,
        self::STATUS_VALIDATED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'member_id', nullable: false)]
    private Member $member;

    #[ORM\Column(name: 'issued_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $issuedAt;

    #[ORM\Column(name: 'received_at', type: 'datetime_immutable', nullable: true)]
    #[Gedmo\Versioned]
    private ?\DateTimeImmutable $receivedAt = null;

    #[ORM\Column(name: 'amount', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string|int|float $amount = 0;

    #[ORM\Column(name: 'identifier', length: 255)]
    #[Gedmo\Versioned]
    private string $identifier = '';

    #[ORM\Column(name: 'notes', type: Types::TEXT)]
    #[Gedmo\Versioned]
    private string $notes = '';

    #[ORM\Column(name: 'method', length: 20)]
    #[Gedmo\Versioned]
    private ?string $method = self::METHOD_CASH;

    #[ORM\Column(name: 'checkout_id', type: 'string', length: 250, unique: true, nullable: true)]
    protected ?string $checkoutId = null;

    #[ORM\Column(name: 'status', type: 'string', length: 10, options: ['default' => self::STATUS_PENDING])]
    #[Gedmo\Versioned]
    protected string $status = self::STATUS_PENDING;

    /**
     * @var Collection<PaymentOrder>
     */
    #[ORM\OneToMany(targetEntity: PaymentOrder::class, mappedBy: 'payment')]
    private Collection $orders;

    public function __construct()
    {
        $this->issuedAt = date_create_immutable('now 00:00:00');
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIssuedAt(): \DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(\DateTimeImmutable $issuedAt): static
    {
        $this->issuedAt = $issuedAt;

        return $this;
    }

    public function getReceivedAt(): ?\DateTimeImmutable
    {
        return $this->receivedAt;
    }

    public function setReceivedAt(?\DateTimeImmutable $receivedAt): static
    {
        $this->receivedAt = $receivedAt;

        return $this;
    }

    public function getAmount(): float
    {
        return (float) $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): static
    {
        $this->identifier = $identifier;

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

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): static
    {
        if (!\in_array($method, static::METHODS)) {
            throw new \InvalidArgumentException("Unexpected method {$method}.");
        }
        $this->method = $method;

        return $this;
    }

    public function getCheckoutId(): ?string
    {
        return $this->checkoutId;
    }

    public function setCheckoutId(?string $checkoutId): static
    {
        $this->checkoutId = $checkoutId;

        return $this;
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
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<PaymentOrder>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function getLogConcernedMember(): Member
    {
        return $this->getMember();
    }

    public function getFriendlyName(): string
    {
        return 'Paiement '.$this->getIdentifier();
    }
}
