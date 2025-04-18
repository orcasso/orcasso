<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomCollectionType extends AbstractType
{
    public function getParent(): ?string
    {
        return \Symfony\Component\Form\Extension\Core\Type\CollectionType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'forms',
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'attr' => ['class' => 'form-jq-collection'],
        ]);
    }
}
