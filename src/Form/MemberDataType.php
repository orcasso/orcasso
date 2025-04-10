<?php

namespace App\Form;

use App\Model\MemberData;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberDataType extends MemberType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MemberData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
