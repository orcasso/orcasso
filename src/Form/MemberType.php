<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('gender', ChoiceType::class, [
                'label' => 'member.label.gender',
                'choices' => Member::GENDERS,
                'choice_label' => fn (string $gender) => "member.choice.gender.{$gender}",
            ])
            ->add('firstName', TextType::class, [
                'label' => 'member.label.first_name',
                'empty_data' => '',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'member.label.last_name',
                'empty_data' => '',
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'member.label.birth_date',
                'widget' => 'single_text',
                'empty_data' => '',
            ])
            ->add('email', EmailType::class, [
                'label' => 'member.label.email',
                'empty_data' => '',
            ])
            ->add('phoneNumber', TelType::class, [
                'label' => 'member.label.phone_number',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('street1', TextType::class, [
                'label' => 'member.label.street1',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('street2', TextType::class, [
                'label' => 'member.label.street2',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('street3', TextType::class, [
                'label' => 'member.label.street3',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'member.label.postal_code',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('city', TextType::class, [
                'label' => 'member.label.city',
                'required' => false,
                'empty_data' => '',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
            'translation_domain' => 'forms',
        ]);
    }
}
