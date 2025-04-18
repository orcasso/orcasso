<?php

namespace App\Dev\DataFixtures;

use App\Entity\LegalRepresentative;
use App\Entity\Member;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

/**
 * @codeCoverageIgnore
 */
class LegalRepresentativeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        foreach ($manager->getRepository(Member::class)->findAll() as $member) {
            if ($member->getAge() >= 18) {
                continue;
            }
            for ($i = 1; $i <= random_int(1, 2); ++$i) {
                $representative = new LegalRepresentative($member);
                $representative
                    ->setEmail(random_int(0, 1) > 0 ? $member->getEmail() : $faker->email())
                    ->setFirstName($faker->firstName)
                    ->setLastName(random_int(0, 5) > 0 ? $member->getLastName() : $faker->lastName)
                    ->setPhoneNumber(random_int(0, 1) > 0 ? $member->getPhoneNumber() : $faker->phoneNumber)
                ;
                $manager->persist($representative);
            }
            $manager->flush();
        }
    }

    public function getDependencies(): array
    {
        return [MemberFixtures::class];
    }
}
