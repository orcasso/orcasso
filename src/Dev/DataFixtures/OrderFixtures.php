<?php

namespace App\Dev\DataFixtures;

use App\Entity\Member;
use App\Entity\Order;
use App\Entity\OrderLine;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

/**
 * @codeCoverageIgnore
 */
class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    protected \Faker\Generator $faker;

    public function load(ObjectManager $manager): void
    {
        $this->faker = Factory::create('fr_FR');

        $index = 0;
        foreach ($manager->getRepository(Member::class)->findBy([], ['id' => 'ASC']) as $member) {
            ++$index;
            if (UserFixtures::USERS[0] === $member->getEmail() || $index > 10) {
                continue;
            }

            $order = $this->createOrder($member);
            $manager->persist($order);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [MemberFixtures::class, ActivityFixtures::class];
    }

    protected function createOrder(Member $member): Order
    {
        $order = (new Order())
            ->setMember($member)
            ->setNotes($this->faker->text)
            ->setStatus(Order::STATUS_VALIDATED)
            ->setCreatedAt($orderDate = $this->faker->dateTimeBetween('first day of january'))
            ->setUpdatedAt($orderDate)
        ;
        OrderLine::createSimple($order)->setLabel('Don exceptionnel')->setAmount($this->faker->randomFloat(0, 50, 200));

        return $order;
    }
}
