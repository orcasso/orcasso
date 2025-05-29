<?php

namespace App\Form;

use App\Entity\Configuration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Configuration $configuration */
        $configuration = $options['data'];
        $type = Configuration::ITEMS_FORM_TYPES[$configuration->getItem()];
        $options = ['label' => 'configuration.label.'.$configuration->getItem()];
        if (TextareaType::class === $type) {
            $options['attr'] = ['rows' => 5];
        }

        $builder->add('value', $type, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Configuration::class,
            'translation_domain' => 'forms',
        ]);
    }
}
