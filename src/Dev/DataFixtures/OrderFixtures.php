<?php

namespace App\Dev\DataFixtures;

use App\Entity\Activity;
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

        foreach ($manager->getRepository(Member::class)->findAll() as $member) {
            if (UserFixtures::USERS[0] === $member->getEmail() || random_int(0, 1)) {
                continue;
            }

            $order = $this->createOrder($member);
            $order->setStatus(random_int(0, 4) ? Order::STATUS_VALIDATED : Order::STATUS_PENDING);
            $order->setStatus(random_int(0, 20) ? $order->getStatus() : Order::STATUS_CANCELLED);
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
        $order = new Order();
        $order->setTotalAmount($this->faker->randomFloat(2, 100, 800));
        $order->setMember($member);
        $order->setNotes($this->faker->text);

        $mainLine = OrderLine::createSimple($order)->setLabel('Cursus complet')->setAmount(573);
        if (random_int(0, 1)) {
            OrderLine::createAllowance($order)->setLabel('Remise quotient familial')
                ->setAllowancePercentage($this->faker->randomElement([5, 10, 15]))
                ->setAllowanceBaseAmount($mainLine->getAmount())
            ;
        }
        $instrument = $this->getReference($this->faker->randomElement(ActivityFixtures::INSTRUMENTS), Activity::class);
        OrderLine::createActivitySubscription($order, $instrument);

        return $order;
    }
}
