<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:user:create', description: 'Creates a new user.')]
class UserCreateCommand extends Command
{
    public function __construct(protected UserRepository $repository, protected UserPasswordHasherInterface $hasher)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $input->isInteractive() ? $io->title('Creates a new user.') : $io->newLine();

        $email = $io->ask('Email', '', function (string $email) {
            $email = trim($email);
            if (!filter_var($email, \FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Invalid email.');
            }

            return $email;
        });

        $name = $io->ask('Name', $email);

        $password = $io->askHidden('Plain password', function ($password) {
            if ('' === trim($password)) {
                throw new \Exception('The password must not be empty.');
            }

            return trim($password);
        });

        $user = new User();
        $user->setEmail($email)->setName($name);
        $user->setPassword($this->hasher->hashPassword($user, $password));
        $user->setRoles(User::ROLES);
        $this->repository->update($user);

        $io->success(\sprintf('User created : "%s" : %s', $user->getEmail(), $user->getId()));

        return Command::SUCCESS;
    }
}
