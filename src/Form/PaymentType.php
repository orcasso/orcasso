<?php

namespace App\Form;

use App\Entity\Member;
use App\Entity\Payment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Payment $payment */
        $payment = $options['data'];

        $builder
            ->add('member', EntityType::class, [
                'label' => 'payment.label.member',
                'class' => Member::class,
                'choice_label' => fn (Member $member) => $member->getFullName(),
                'attr' => ['class' => 'select2-select'],
            ])
            ->add('identifier', TextType::class, [
                'label' => 'payment.label.identifier',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'payment.label.notes',
                'attr' => ['rows' => 5],
                'required' => false,
                'empty_data' => '',
            ])
            ->add('method', ChoiceType::class, [
                'label' => 'payment.label.method',
                'choices' => Payment::METHODS,
                'choice_label' => fn (string $method) => "payment.choice.method.{$method}",
            ])
        ;
        if (!$payment->getOrders()->isEmpty()) {
            $builder
                ->add('amount', MoneyType::class, [
                    'label' => 'payment_order.label.amount',
                    'attr' => ['class' => 'col-lg-3'],
                    'property_path' => 'orders[0].amount',
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
            'translation_domain' => 'forms',
        ]);
    }
}
