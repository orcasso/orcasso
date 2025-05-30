<?php

namespace App\Form;

use App\Entity\Member;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('member', EntityType::class, [
                'label' => 'order.label.member',
                'class' => Member::class,
                'choice_label' => fn (Member $member) => $member->getFullName(),
                'attr' => ['class' => 'select2-select'],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'order.label.notes',
                'attr' => ['rows' => 5],
                'required' => false,
                'empty_data' => '',
            ])
            ->add('totalAmount', MoneyType::class, [
                'label' => 'order.label.total_amount',
                'attr' => ['readonly' => 'readonly', 'class' => 'col-lg-3'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'translation_domain' => 'forms',
        ]);
    }
}
