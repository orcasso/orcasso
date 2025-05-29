<?php

namespace App\Entity;

use App\Repository\ConfigurationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

#[ORM\Table(name: 't_configuration')]
#[ORM\Entity(repositoryClass: ConfigurationRepository::class)]
class Configuration
{
    use TimestampableEntity;

    public const ITEM_PAYMENT_METHOD_CHEQUE_INSTRUCTION = 'payment_method_cheque_instruction';
    public const ITEM_PAYMENT_METHOD_BANK_TRANSFER_IBAN = 'payment_method_bank_transfer_iban';
    public const ITEM_PAYMENT_METHOD_BANK_TRANSFER_BIC = 'payment_method_bank_transfer_bic';

    public const ITEMS_FORM_TYPES = [
        self::ITEM_PAYMENT_METHOD_CHEQUE_INSTRUCTION => TextareaType::class,
        self::ITEM_PAYMENT_METHOD_BANK_TRANSFER_IBAN => TextType::class,
        self::ITEM_PAYMENT_METHOD_BANK_TRANSFER_BIC => TextType::class,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'item', type: 'string', length: 255, unique: true)]
    protected string $item = '';

    #[ORM\Column(name: 'value', type: 'text')]
    protected string $value = '';

    public function __construct(string $item)
    {
        $this->item = $item;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItem(): ?string
    {
        return $this->item;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
