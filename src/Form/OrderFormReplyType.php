<?php

namespace App\Form;

use App\Entity\OrderFormFieldChoice;
use App\Entity\OrderFormReply;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            $choiceValue = function (?OrderFormFieldChoice $choice) {
                if (null === $choice) {
                    return null;
                }

                return $choice->getActivity()?->getName() ?? $choice->getAllowanceLabel();
            };

            $choices = array_map($choiceValue, $field->getChoices()->toArray());
            $choices = array_combine($choices, $choices);

            $builder
                ->add('fieldValues_'.$field->getPosition(), ChoiceType::class, [
                    'label' => $field->getQuestion(),
                    'translation_domain' => null,
                    'mapped' => false,
                    'choices' => $choices,
                    'required' => $field->isRequired(),
                    'placeholder' => '--',
                ]);

            $builder
                ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($reply) {
                    foreach ($reply->getForm()->getFields() as $field) {
                        $fieldValue = $event->getForm()->get('fieldValues_'.$field->getPosition())->getData();
                        $reply->setFieldValue($field->getQuestion(), $fieldValue ?? '');
                    }
                })
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderFormReply::class,
        ]);
    }
}
