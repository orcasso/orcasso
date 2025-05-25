<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: 't_user')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, Timestampable
{
    use TimestampableEntity;

    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN_ACTIVITY_EDIT = 'ROLE_ADMIN_ACTIVITY_EDIT';
    public const ROLE_ADMIN_CONFIGURATION_EDIT = 'ROLE_ADMIN_CONFIGURATION_EDIT';
    public const ROLE_ADMIN_MEMBER_EDIT = 'ROLE_ADMIN_MEMBER_EDIT';
    public const ROLE_ADMIN_ORDER_EDIT = 'ROLE_ADMIN_ORDER_EDIT';
    public const ROLE_ADMIN_ORDER_FORM_EDIT = 'ROLE_ADMIN_ORDER_FORM_EDIT';
    public const ROLE_ADMIN_PAYMENT_EDIT = 'ROLE_ADMIN_PAYMENT_EDIT';
    public const ROLE_ADMIN_USER_EDIT = 'ROLE_ADMIN_USER_EDIT';

    public const ROLES = [
        self::ROLE_USER,
        self::ROLE_ADMIN_ACTIVITY_EDIT,
        self::ROLE_ADMIN_CONFIGURATION_EDIT,
        self::ROLE_ADMIN_MEMBER_EDIT,
        self::ROLE_ADMIN_ORDER_EDIT,
        self::ROLE_ADMIN_ORDER_FORM_EDIT,
        self::ROLE_ADMIN_PAYMENT_EDIT,
        self::ROLE_ADMIN_USER_EDIT,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'email', type: 'string', length: 255, unique: true)]
    public string $email;

    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    public string $name;

    #[ORM\Column(name: 'roles', type: 'simple_array', nullable: true)]
    private array $roles = [self::ROLE_USER];

    #[ORM\Column(name: 'password', type: 'string', length: 255)]
    private ?string $password = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return array_unique(array_merge($this->roles, [static::ROLE_USER]));
    }

    public function setRoles(array $roles): static
    {
        $this->roles = array_unique(array_merge($roles, [static::ROLE_USER]));

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return \in_array($role, $this->roles);
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
