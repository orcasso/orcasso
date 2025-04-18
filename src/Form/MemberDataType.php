<?php

namespace App\Form;

use App\Form\Type\CustomCollectionType;
use App\Model\MemberData;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberDataType extends MemberType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('legalRepresentatives', CustomCollectionType::class, [
                'label' => 'member.label.legal_representatives',
                'entry_type' => LegalRepresentativeDataType::class,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MemberData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
