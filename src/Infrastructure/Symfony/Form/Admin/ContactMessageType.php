<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Symfony\Form\Admin;

use AlexandreBulete\DddContactBundle\Domain\Model\MessageStatus;
use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Resource\ContactMessageResource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The message as sent, read-only; its filing below.
 */
final class ContactMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $readOnly = ['disabled' => true, 'required' => false];
        $builder
            ->add('senderName', TextType::class, ['section' => 'message', 'span' => 'half', 'label' => 'contact.message.sender_name'] + $readOnly)
            ->add('senderEmail', TextType::class, ['section' => 'message', 'span' => 'half', 'label' => 'contact.message.sender_email'] + $readOnly)
            ->add('subject', TextType::class, ['section' => 'message', 'label' => 'contact.message.subject'] + $readOnly)
            ->add('body', TextareaType::class, ['section' => 'message', 'label' => 'contact.message.body', 'attr' => ['rows' => 12]] + $readOnly)
            ->add('status', EnumType::class, [
                'section' => 'handling',
                'class' => MessageStatus::class,
                'label' => 'contact.message.status',
                'choice_label' => static fn (MessageStatus $status): string => 'contact.message.status_' . $status->value,
            ])
            ->add('spam', CheckboxType::class, [
                'section' => 'handling',
                'switch' => true,
                'label' => 'contact.message.spam',
                'help' => 'contact.message.spam_help',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactMessageResource::class,
            'sections' => [
                'message' => ['label' => 'contact.section.message'],
                'handling' => ['label' => 'contact.section.handling', 'column' => 'side'],
            ],
        ]);
    }
}
