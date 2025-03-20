<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Table(name: 't_order')]
#[ORM\Entity(repositoryClass: OrderRepository::class)]
class Order
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'notes', type: 'text', options: ['default' => ''])]
    protected string $notes = '';

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id', nullable: false)]
    private Member $member;

    #[ORM\Column(name: 'total_amount', type: 'decimal', precision: 10, scale: 2)]
    protected string|int|float $totalAmount = 0;

    #[ORM\Column(name: 'paid_amount', type: 'decimal', precision: 10, scale: 2)]
    protected string|int|float $paidAmount = 0;

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

    public function __construct()
    {
        $this->lines = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
}
