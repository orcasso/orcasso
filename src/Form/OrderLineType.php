<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\OrderLine;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var OrderLine $line */
        $line = $options['data'];
        $builder->add('label', TextType::class, [
            'label' => 'order_line.label.label',
        ]);

        if (OrderLine::TYPE_ACTIVITY_SUBSCRIPTION === $line->getType()) {
            $builder
                ->add('subscribedActivity', EntityType::class, [
                    'label' => 'order_line.label.subscribed_activity',
                    'class' => Activity::class,
                    'choice_label' => 'name',
                    'attr' => ['class' => 'select2-select'],
                ])
                ->remove('label');
        }

        $builder
            ->add('amount', MoneyType::class, [
                'label' => 'order.label.total_amount',
                'attr' => ['class' => 'col-lg-3'],
            ])
        ;

        if (OrderLine::TYPE_ALLOWANCE === $line->getType()) {
            $builder
                ->add('allowancePercentage', PercentType::class, [
                    'label' => 'order_line.label.allowance_percentage',
                    'attr' => ['class' => 'col-lg-3'],
                    'type' => 'integer',
                    'scale' => 2,
                ])
                ->add('allowanceBaseAmount', MoneyType::class, [
                    'label' => 'order_line.label.allowance_base_amount',
                    'attr' => ['class' => 'col-lg-3'],
                ])
                ->remove('amount')
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderLine::class,
            'translation_domain' => 'forms',
        ]);
    }
}
