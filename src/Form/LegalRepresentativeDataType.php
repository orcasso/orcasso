<?php

namespace App\Form;

use App\Model\LegalRepresentativeData;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LegalRepresentativeDataType extends LegalRepresentativeType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(['data_class' => LegalRepresentativeData::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'legal_representative_data_type';
    }
}
