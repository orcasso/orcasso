<?php

namespace App\Form;

use App\Entity\OrderFormField;
use App\Entity\OrderFormFieldChoice;
use App\Entity\OrderFormReply;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class OrderFormReplyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var OrderFormReply $reply */
        $reply = $options['data'];

        $builder
            ->add('memberData', MemberDataType::class, [
                'label' => 'order_form_reply.label.member_data',
            ])
        ;

        foreach ($reply->getForm()->getFields() as $field) {
            $this->buildFieldType($builder, $field, $reply);

            $builder
                ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($reply) {
                    foreach ($reply->getForm()->getFields() as $field) {
                        $fieldValue = $event->getForm()->get(static::getFieldName($field))->getData();
                        if ($fieldValue instanceof UploadedFile) {
                            continue;
                        }
                        $reply->setFieldValue($field->getQuestion(), $fieldValue ?? '');
                    }
                })
            ;
        }

        $builder->add('notes', TextareaType::class, [
            'label' => 'order_form_reply.label.notes',
            'attr' => ['rows' => 5],
            'required' => false,
            'empty_data' => '',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderFormReply::class,
            'translation_domain' => 'forms',
        ]);
    }

    public static function getFieldName(OrderFormField $field): string
    {
        return "fieldValues_{$field->getPosition()}";
    }

    protected function buildFieldType(FormBuilderInterface $builder, OrderFormField $field, OrderFormReply $reply): void
    {
        if (OrderFormField::TYPE_DOCUMENT === $field->getType()) {
            $builder
                ->add(static::getFieldName($field), FileType::class, [
                    'label' => $field->getQuestion(),
                    'required' => $field->isRequired(),
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
                ]);

            return;
        }

        $choiceValue = function (?OrderFormFieldChoice $choice) {
            if (null === $choice) {
                return null;
            }

            return $choice->getActivity()?->getName() ?? $choice->getAllowanceLabel();
        };

        $choices = array_map($choiceValue, $field->getChoices()->toArray());
        $choices = array_combine($choices, $choices);

        $builder
            ->add(static::getFieldName($field), ChoiceType::class, [
                'label' => $field->getQuestion(),
                'translation_domain' => null,
                'mapped' => false,
                'choices' => $choices,
                'required' => $field->isRequired(),
                'placeholder' => '--',
            ]);
    }
}
