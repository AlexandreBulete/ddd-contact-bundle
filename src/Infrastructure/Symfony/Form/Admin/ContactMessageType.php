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
            ->add('senderName', TextType::class, ['label' => 'contact.message.sender_name'] + $readOnly)
            ->add('senderEmail', TextType::class, ['label' => 'contact.message.sender_email'] + $readOnly)
            ->add('subject', TextType::class, ['label' => 'contact.message.subject'] + $readOnly)
            ->add('body', TextareaType::class, ['label' => 'contact.message.body', 'attr' => ['rows' => 12]] + $readOnly)
            ->add('status', EnumType::class, [
                'class' => MessageStatus::class,
                'label' => 'contact.message.status',
                'choice_label' => static fn (MessageStatus $status): string => 'contact.message.status_' . $status->value,
            ])
            ->add('spam', CheckboxType::class, [
                'label' => 'contact.message.spam',
                'help' => 'contact.message.spam_help',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ContactMessageResource::class]);
    }
}
