<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Table(name: 't_member')]
#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[Gedmo\Loggable(logEntryClass: MemberLog::class)]
class Member implements MemberLogObjectInterface
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
    #[Gedmo\Versioned]
    protected string $gender = self::GENDER_MALE;

    #[ORM\Column(name: 'first_name', type: 'string', length: 255)]
    #[Gedmo\Versioned]
    protected string $firstName = '';

    #[ORM\Column(name: 'last_name', type: 'string', length: 255)]
    #[Gedmo\Versioned]
    protected string $lastName = '';

    #[ORM\Column(name: 'birth_date', type: 'date_immutable')]
    #[Gedmo\Versioned]
    protected \DateTimeImmutable $birthDate;

    #[ORM\Column(name: 'email', type: 'string', length: 255)]
    #[Gedmo\Versioned]
    protected string $email = '';

    #[ORM\Column(name: 'phone_number', type: 'string', length: 35)]
    #[Gedmo\Versioned]
    protected string $phoneNumber = '';

    #[ORM\Column(name: 'street1', type: 'string', length: 255)]
    #[Gedmo\Versioned]
    protected string $street1 = '';

    #[ORM\Column(name: 'street2', type: 'string', length: 255)]
    #[Gedmo\Versioned]
    protected string $street2 = '';

    #[ORM\Column(name: 'street3', type: 'string', length: 255)]
    #[Gedmo\Versioned]
    protected string $street3 = '';

    #[ORM\Column(name: 'postal_code', type: 'string', length: 10)]
    #[Gedmo\Versioned]
    protected string $postalCode = '';

    #[ORM\Column(name: 'city', type: 'string', length: 255)]
    #[Gedmo\Versioned]
    protected string $city = '';

    /**
     * @var Collection<int, MemberDocument>
     */
    #[ORM\OneToMany(targetEntity: MemberDocument::class, mappedBy: 'member', orphanRemoval: true)]
    private Collection $documents;

    /**
     * @var Collection<int, LegalRepresentative>
     */
    #[ORM\OneToMany(targetEntity: LegalRepresentative::class, mappedBy: 'member', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $legalRepresentatives;

    public function __construct()
    {
        $this->birthDate = date_create_immutable();
        $this->documents = new ArrayCollection();
        $this->legalRepresentatives = new ArrayCollection();
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

    public function getAge(): int
    {
        return $this->birthDate->diff(date_create_immutable())->y;
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

    public function getStreets(string $separator = \PHP_EOL): string
    {
        return trim(implode($separator, array_filter([$this->street1, $this->street2, $this->street3])));
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

    public function getFullAddress(string $streetsSeparator = \PHP_EOL, string $linesSeparator = \PHP_EOL): string
    {
        return trim(implode($linesSeparator, array_filter([
            $this->getStreets($streetsSeparator),
            implode(' ', array_filter([$this->postalCode, $this->city])),
        ])));
    }

    /**
     * @return Collection<int, MemberDocument>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(MemberDocument $filename): static
    {
        if (!$this->documents->contains($filename)) {
            $this->documents->add($filename);
            $filename->setMember($this);
        }

        return $this;
    }

    public function removeDocument(MemberDocument $filename): static
    {
        if ($this->documents->removeElement($filename)) {
            if ($filename->getMember() === $this) {
                $filename->setMember(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LegalRepresentative>
     */
    public function getLegalRepresentatives(): Collection
    {
        return $this->legalRepresentatives;
    }

    public function addLegalRepresentative(LegalRepresentative $representative): static
    {
        if (!$this->legalRepresentatives->contains($representative)) {
            $this->legalRepresentatives->add($representative);
        }

        return $this;
    }

    public function removeLegalRepresentative(LegalRepresentative $representative): static
    {
        $this->legalRepresentatives->removeElement($representative);

        return $this;
    }

    public function getLogConcernedMember(): self
    {
        return $this;
    }
}
