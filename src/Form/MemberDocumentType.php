<?php

namespace App\Form;

use App\Entity\MemberDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class MemberDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options['data']->getId()) {
            $builder
                ->add('file', FileType::class, [
                    'label' => '_meta.file',
                    'mapped' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '2048k',
                            'mimeTypes' => [
                                'application/pdf',
                                'application/x-pdf',
                                'image/jpeg',
                                'image/png',
                            ],
                        ]),
                    ],
                ])
            ;
        }

        $builder
            ->add('name', TextType::class, [
                'label' => 'member_document.label.name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MemberDocument::class,
            'translation_domain' => 'forms',
        ]);
    }
}
