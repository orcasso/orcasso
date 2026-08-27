<?php

namespace App\Form;

use App\Entity\OrderFormField;
use App\Entity\OrderFormFieldChoice;
use App\Entity\OrderFormReply;
use App\Utils\AntiSpam;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderFormReplyType extends AbstractType
{
    public function __construct(
        private readonly AntiSpam $antiSpam,
        private readonly TranslatorInterface $translator)
    {
    }

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

        $this->buildAntiSpamFields($builder);
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

    /**
     * Adds a honeypot field (hidden from humans by CSS, usually filled by bots)
     * and a signed timestamp rejecting submissions faster than a human can type.
     */
    protected function buildAntiSpamFields(FormBuilderInterface $builder): void
    {
        $builder
            ->add('website', TextType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'row_attr' => ['class' => 'd-none'],
                'attr' => ['autocomplete' => 'off', 'tabindex' => '-1'],
            ])
            ->add('antiSpamToken', HiddenType::class, [
                'mapped' => false,
                'data' => $this->antiSpam->generateToken(),
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                if (!$form->get('website')->getData() && $this->antiSpam->isTokenValid($form->get('antiSpamToken')->getData())) {
                    return;
                }
                $form->addError(new FormError($this->translator->trans('order_form_reply.error.anti_spam', [], 'forms')));
            })
        ;
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
