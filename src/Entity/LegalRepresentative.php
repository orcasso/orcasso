<?php

namespace App\Entity;

use App\Repository\LegalRepresentativeRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Table(name: 't_legal_representative')]
#[ORM\Entity(repositoryClass: LegalRepresentativeRepository::class)]
class LegalRepresentative
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'first_name', type: 'string', length: 255)]
    protected string $firstName = '';

    #[ORM\Column(name: 'last_name', type: 'string', length: 255)]
    protected string $lastName = '';

    #[ORM\Column(name: 'email', type: 'string', length: 255)]
    protected string $email = '';

    #[ORM\Column(name: 'phone_number', type: 'string', length: 35)]
    protected string $phoneNumber = '';

    #[ORM\ManyToOne(targetEntity: Member::class, inversedBy: 'legalRepresentatives')]
    #[ORM\JoinColumn(name: 'member_id', nullable: false)]
    protected Member $member;

    public function __construct(Member $member)
    {
        $this->member = $member;
        $this->member->addLegalRepresentative($this);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getMember(): Member
    {
        return $this->member;
    }
}
