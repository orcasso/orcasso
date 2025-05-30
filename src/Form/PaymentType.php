<?php

namespace App\Form;

use App\Entity\Member;
use App\Entity\Payment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('member', EntityType::class, [
                'label' => 'payment.label.member',
                'class' => Member::class,
                'choice_label' => fn (Member $member) => $member->getFullName(),
                'attr' => ['class' => 'select2-select'],
            ])
            ->add('issuedAt', DateType::class, [
                'label' => 'payment.label.issued_at',
                'widget' => 'single_text',
            ])
            ->add('receivedAt', DateType::class, [
                'label' => 'payment.label.received_at',
                'widget' => 'single_text',
                'required' => false,
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
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
            'translation_domain' => 'forms',
        ]);
    }
}
