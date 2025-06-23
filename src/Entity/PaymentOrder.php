<?php

namespace App\Entity;

use App\Repository\PaymentOrderRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Table(name: 't_payment_order')]
#[ORM\Entity(repositoryClass: PaymentOrderRepository::class)]
#[Gedmo\Loggable(logEntryClass: MemberLog::class)]
class PaymentOrder implements MemberLogObjectInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Payment::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(name: 'payment_id', referencedColumnName: 'id', nullable: false)]
    private Payment $payment;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false)]
    private ?Order $order = null;

    #[ORM\Column(name: 'amount', type: 'decimal', precision: 10, scale: 2)]
    #[Gedmo\Versioned]
    private string|int|float $amount = 0;

    public function __construct(Payment $payment, ?Order $order = null)
    {
        $this->payment = $payment;
        if ($order) {
            $this->setOrder($order);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): self
    {
        if ($order::STATUS_CANCELLED === $order->getStatus()) {
            throw new \RuntimeException('Unable to add payment to cancelled order');
        }
        $this->order = $order;

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

    public function getLogConcernedMember(): Member
    {
        return $this->getOrder()->getMember();
    }

    public function getFriendlyName(): string
    {
        return $this->payment->getFriendlyName().' > '.$this->order->getFriendlyName();
    }
}
