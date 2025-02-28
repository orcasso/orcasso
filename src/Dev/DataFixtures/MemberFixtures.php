<?php

namespace App\Dev\DataFixtures;

use App\Entity\Member;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

/**
 * @codeCoverageIgnore
 */
class MemberFixtures extends Fixture
{
    public const COUNT = 100;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < self::COUNT; ++$i) {
            $member = new Member();
            $member
                ->setGender($faker->randomElement(Member::GENDERS))
                ->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setBirthDate(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-80 years', '-3 years')))
                ->setPhoneNumber($faker->phoneNumber)
                ->setEmail(0 === $i ? UserFixtures::USERS[0] : $faker->email)
                ->setStreet1($faker->streetAddress)
                ->setStreet2(random_int(0, 1) ? $faker->secondaryAddress : '')
                ->setStreet3($member->getStreet2() && random_int(0, 1) ? $faker->secondaryAddress : '')
                ->setCity($faker->city)
                ->setPostalCode($faker->postcode)
            ;
            $manager->persist($member);
            $this->addReference('member_'.$i, $member);
        }
        $manager->flush();
    }
}
