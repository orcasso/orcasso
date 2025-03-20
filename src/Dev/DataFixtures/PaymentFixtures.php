<?php

namespace App\Dev\DataFixtures;

use App\Entity\Activity;
use App\Entity\Member;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\Payment;
use App\Entity\PaymentOrder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

/**
 * @codeCoverageIgnore
 */
class PaymentFixtures extends Fixture implements DependentFixtureInterface
{
    protected \Faker\Generator $faker;

    public function load(ObjectManager $manager): void
    {
        $this->faker = Factory::create('fr_FR');

        foreach ($manager->getRepository(Order::class)->findAll() as $order) {
            if (random_int(0, 4)) {
                continue;
            }

            $payment = (new Payment());
            $payment
                ->setNotes($this->faker->text)
                ->setIdentifier($this->faker->uuid)
                ->setMethod($this->faker->randomElement(Payment::METHODS))
                ->setMember($order->getMember())
                ->setIssuedAt(\DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-2 months')))
                ->setReceivedAt($payment->getIssuedAt())
            ;

            $paymentOrder = (new PaymentOrder($payment, $order))
                ->setAmount(random_int(0, 3) ? $order->getTotalAmount() : $this->faker->numberBetween(10, $order->getTotalAmount()));
            $manager->persist($payment);
            $manager->persist($paymentOrder);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [OrderFixtures::class];
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
