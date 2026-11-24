<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Application\Command\ReceiveContactMessage;

use AlexandreBulete\DddFoundation\Application\Authorization\PublicAccess;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddContactBundle\Domain\ValueObject\ContactMessageId;

/**
 * Keeps what a visitor sent through the contact form. Open to anyone: the
 * form, not a permission, is the gate (see the spam checks of the form).
 *
 * @implements CommandInterface<ContactMessage>
 */
#[PublicAccess]
final readonly class ReceiveContactMessageCommand implements CommandInterface
{
    public function __construct(
        public ContactMessageId $id,
        public string $senderName,
        public string $senderEmail,
        public ?string $subject,
        public string $body,
        public bool $spam = false,
    ) {}
}
