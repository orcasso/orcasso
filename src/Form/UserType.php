<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'user.label.name',
            ])
            ->add('email', EmailType::class, [
                'label' => 'user.label.email',
                'disabled' => (bool) $options['data']->getId(),
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => !$options['data']->getId(),
                'mapped' => false,
                'first_options' => ['label' => 'user.label.password'],
                'second_options' => ['label' => 'user.label.confirm_password'],
                'constraints' => [new Length(['min' => 8, 'max' => 4096])],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'user.label.roles',
                'multiple' => true,
                'expanded' => true,
                'choices' => User::ROLES,
                'choice_label' => fn (string $role) => 'user.choice.roles.'.$role,
                'choice_attr' => fn (string $role) => User::ROLE_USER === $role ? ['disabled' => 'disabled'] : [],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'forms',
        ]);
    }
}
