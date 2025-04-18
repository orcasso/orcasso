<?php

namespace App\Form;

use App\Entity\LegalRepresentative;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LegalRepresentativeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'member.label.first_name',
                'empty_data' => '',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'member.label.last_name',
                'empty_data' => '',
            ])
            ->add('email', EmailType::class, [
                'label' => 'member.label.email',
                'empty_data' => '',
            ])
            ->add('phoneNumber', TelType::class, [
                'label' => 'member.label.phone_number',
                'empty_data' => '',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LegalRepresentative::class,
            'translation_domain' => 'forms',
        ]);
    }
}
