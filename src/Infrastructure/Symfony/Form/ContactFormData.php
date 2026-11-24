<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Symfony\Form;

use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The public form, as typed.
 */
final class ContactFormData
{
    #[Assert\NotBlank(message: 'contact.form.name_blank')]
    #[Assert\Length(max: ContactMessage::NAME_MAX_LENGTH)]
    public ?string $name = null;

    #[Assert\NotBlank(message: 'contact.form.email_blank')]
    #[Assert\Email(message: 'contact.form.email_invalid')]
    #[Assert\Length(max: ContactMessage::EMAIL_MAX_LENGTH)]
    public ?string $email = null;

    #[Assert\Length(max: ContactMessage::SUBJECT_MAX_LENGTH)]
    public ?string $subject = null;

    #[Assert\NotBlank(message: 'contact.form.message_blank')]
    #[Assert\Length(min: 10, max: ContactMessage::BODY_MAX_LENGTH)]
    public ?string $message = null;
}
