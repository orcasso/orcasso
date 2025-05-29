<?php

namespace App\Form;

use App\Entity\Configuration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Configuration $configuration */
        foreach ($options['data'] as $configuration) {
            $builder
                ->add($configuration->getItem(), ConfigurationType::class, [
                    'label' => false,
                    'data' => $configuration,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'forms',
        ]);
    }
}
