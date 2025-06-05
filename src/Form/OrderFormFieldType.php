<?php

namespace App\Form;

use App\Entity\OrderFormField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFormFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var OrderFormField $field */
        $field = $options['data'];
        if (!$field->getId()) {
            $builder
                ->add('type', ChoiceType::class, [
                    'label' => 'order_form_field.label.type',
                    'choices' => OrderFormField::TYPES,
                    'choice_label' => fn (string $type) => "order_form_field.choice.type.{$type}",
                ])
            ;
        }

        $builder
            ->add('question', TextType::class, [
                'label' => 'order_form_field.label.question',
            ])
            ->add('required', CheckboxType::class, [
                'label' => 'order_form_field.label.required',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderFormField::class,
            'translation_domain' => 'forms',
        ]);
    }
}
