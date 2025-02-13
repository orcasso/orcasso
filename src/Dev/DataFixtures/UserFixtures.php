<?php

namespace App\Dev\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @codeCoverageIgnore
 */
class UserFixtures extends Fixture
{
    public const USERS = [
        'benjamin@domain.net',
        'user@domain.net',
    ];

    /**
     * Constructor.
     */
    public function __construct(protected UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (static::USERS as $index => $email) {
            $user = new User();
            $manager->persist($user
                ->setEmail($email)
                ->setName(strtok($email, '@'))
                ->setPassword($this->passwordHasher->hashPassword($user, 'password'))
            );
            if (0 === $index) {
                $user->setRoles([User::ROLE_ADMIN]);
            }
            $this->addReference('user_'.$email, $user);
        }
        $manager->flush();
    }
}
