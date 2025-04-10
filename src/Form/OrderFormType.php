<?php

namespace App\Form;

use App\Entity\OrderForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'order_form.label.title',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'order_form.label.description',
                'attr' => ['rows' => 5],
                'required' => false,
                'empty_data' => '',
            ])
            ->add('orderMainLineLabel', TextType::class, [
                'label' => 'order_form.label.order_main_line_label',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('orderMainLineAmount', MoneyType::class, [
                'label' => 'order_form.label.order_main_line_amount',
                'attr' => ['class' => 'col-lg-3'],
                'required' => false,
                'empty_data' => 0,
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'order_form.label.enabled',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderForm::class,
            'translation_domain' => 'forms',
        ]);
    }
}
