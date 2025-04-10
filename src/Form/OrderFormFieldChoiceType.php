<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\OrderFormField;
use App\Entity\OrderFormFieldChoice;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFormFieldChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var OrderFormFieldChoice $choice */
        $choice = $options['data'];

        if (OrderFormField::TYPE_ACTIVITY_CHOICE === $choice->getField()->getType()) {
            $builder
                ->add('activity', EntityType::class, [
                    'label' => 'order_form_field_choice.label.activity',
                    'class' => Activity::class,
                    'choice_label' => 'name',
                ])
                ->add('activityAmount', MoneyType::class, [
                    'label' => 'order_form_field_choice.label.activity_amount',
                    'attr' => ['class' => 'col-lg-3'],
                    'required' => false,
                    'empty_data' => 0,
                ])
            ;
        } else {
            $builder
                ->add('allowanceLabel', TextType::class, [
                    'label' => 'order_form_field_choice.label.allowance_label',
                    'required' => true,
                ])
                ->add('allowancePercentage', PercentType::class, [
                    'label' => 'order_form_field_choice.label.allowance_percentage',
                    'attr' => ['class' => 'col-lg-3'],
                    'type' => 'integer',
                    'scale' => 2,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderFormFieldChoice::class,
            'translation_domain' => 'forms',
        ]);
    }
}
