<?php

namespace App\Dev\DataFixtures;

use App\Entity\Member;
use App\Entity\Order;
use App\Entity\OrderForm;
use App\Entity\OrderFormField;
use App\Entity\OrderFormFieldChoice;
use App\Entity\OrderFormReply;
use App\Transformer\OrderFormReplyToOrder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

/**
 * @codeCoverageIgnore
 */
class OrderFormReplyFixtures extends Fixture implements DependentFixtureInterface
{
    protected \Faker\Generator $faker;

    public function __construct(protected OrderFormReplyToOrder $replyToOrder)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $this->faker = Factory::create('fr_FR');

        $forms = $manager->getRepository(OrderForm::class)->findAll();
        foreach ($manager->getRepository(Member::class)->findAll() as $member) {
            if (UserFixtures::USERS[0] === $member->getEmail() || random_int(0, 1)) {
                continue;
            }
            /** @var OrderForm $form */
            $form = $this->faker->randomElement($forms);
            $reply = new OrderFormReply($form);
            $reply->setCreatedAt($orderDate = $this->faker->dateTimeBetween('first day of january'))->setUpdatedAt($orderDate);
            $memberData = $reply->getMemberData();
            $memberData->fromMember($member);
            foreach ($form->getFields() as $field) {
                if (!$field->isRequired() && random_int(0, 1)) {
                    continue;
                }
                if (OrderFormField::TYPE_DOCUMENT === $field->getType()) {
                    continue;
                }
                /** @var OrderFormFieldChoice $choice */
                $choice = $this->faker->randomElement($field->getChoices());
                $reply->setFieldValue($field->getQuestion(), $choice->getActivity()?->getName() ?? $choice->getAllowanceLabel());
            }
            $manager->persist($reply);
            $order = $this->replyToOrder->toOrder($reply)->setCreatedAt($orderDate)->setUpdatedAt($orderDate);
            $order->setStatus(random_int(0, 4) ? Order::STATUS_VALIDATED : Order::STATUS_PENDING);
            $order->setStatus(random_int(0, 20) ? $order->getStatus() : Order::STATUS_CANCELLED);
            $manager->persist($order);
            $manager->flush();
        }
    }

    public function getDependencies(): array
    {
        return [OrderFormFixtures::class];
    }
}
