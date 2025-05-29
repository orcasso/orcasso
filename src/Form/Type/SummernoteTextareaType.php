<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SummernoteTextareaType extends AbstractType
{
    public function getParent(): ?string
    {
        return \Symfony\Component\Form\Extension\Core\Type\TextareaType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['rows' => 5, 'class' => 'summernote'],
        ]);
    }
}
