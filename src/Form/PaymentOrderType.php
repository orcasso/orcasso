<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\PaymentOrder;
use App\Repository\OrderRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentOrderType extends AbstractType
{
    public function __construct(protected OrderRepository $orderRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var PaymentOrder $paymentOrder */
        $paymentOrder = $options['data'];

        if (!$paymentOrder->getId()) {
            $builder
                ->add('order', EntityType::class, [
                    'label' => 'payment_order.label.order',
                    'class' => Order::class,
                    'choices' => $this->orderRepository->findWaitingPayment(),
                    'preferred_choices' => fn (Order $order) => $order->getMember() === $paymentOrder->getPayment()->getMember(),
                    'duplicate_preferred_choices' => false,
                    'row_attr' => ['class' => 'mb-5'],
                    'attr' => [
                        'class' => 'select2-select',
                        'data-width' => '100%',
                    ],
                    'choice_label' => fn (Order $order) => $order->getMember()->getFullName().' #'.$order->getId(),
                    'choice_attr' => fn (Order $order) => ['data-due-amount' => ($order->getTotalAmount() - $order->getPaidAmount())],
                    'required' => true,
                ])
            ;
        }

        $builder->add('amount', MoneyType::class, [
            'label' => 'payment_order.label.amount',
            'attr' => ['class' => 'col-lg-3'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentOrder::class,
            'translation_domain' => 'forms',
        ]);
    }
}
