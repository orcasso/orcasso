<?php

namespace App\Dev\DataFixtures;

use App\Entity\Order;
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
            if (Order::STATUS_VALIDATED !== $order->getStatus()) {
                continue;
            }

            $payment = (new Payment());
            $payment
                ->setNotes($this->faker->text)
                ->setIdentifier($this->faker->uuid)
                ->setMethod($this->faker->randomElement(Payment::METHODS))
                ->setMember($order->getMember())
                ->setCreatedAt($this->faker->dateTimeBetween('-2 months'))
                ->setReceivedAt(\DateTimeImmutable::createFromMutable($payment->getCreatedAt()))
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
}
