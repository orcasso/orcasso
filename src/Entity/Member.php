<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
class Member
{
    use TimestampableEntity;

    // genders : up to 10 characters
    public const GENDER_MALE = 'male';
    public const GENDER_FEMALE = 'female';
    public const GENDERS = [
        self::GENDER_MALE,
        self::GENDER_FEMALE,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'gender', type: 'string', length: 10)]
    protected string $gender = self::GENDER_MALE;

    #[ORM\Column(name: 'first_name', type: 'string', length: 255)]
    protected string $firstName = '';

    #[ORM\Column(name: 'last_name', type: 'string', length: 255)]
    protected string $lastName = '';

    #[ORM\Column(name: 'birth_date', type: 'date_immutable')]
    protected \DateTimeImmutable $birthDate;

    #[ORM\Column(name: 'email', type: 'string', length: 255)]
    protected string $email = '';

    #[ORM\Column(name: 'phone_number', type: 'string', length: 35)]
    protected string $phoneNumber = '';

    #[ORM\Column(name: 'street1', type: 'string', length: 255)]
    protected string $street1 = '';

    #[ORM\Column(name: 'street2', type: 'string', length: 255)]
    protected string $street2 = '';

    #[ORM\Column(name: 'street3', type: 'string', length: 255)]
    protected string $street3 = '';

    #[ORM\Column(name: 'postal_code', type: 'string', length: 10)]
    protected string $postalCode = '';

    #[ORM\Column(name: 'city', type: 'string', length: 255)]
    protected string $city = '';

    public function __construct()
    {
        $this->birthDate = date_create_immutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    public function getBirthDate(): \DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeImmutable $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getStreet1(): string
    {
        return $this->street1;
    }

    public function setStreet1(string $street1): static
    {
        $this->street1 = $street1;

        return $this;
    }

    public function getStreet2(): string
    {
        return $this->street2;
    }

    public function setStreet2(string $street2): static
    {
        $this->street2 = $street2;

        return $this;
    }

    public function getStreet3(): string
    {
        return $this->street3;
    }

    public function setStreet3(string $street3): static
    {
        $this->street3 = $street3;

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }
}
