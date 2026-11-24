<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Application\Command\FileContactMessage;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddContactBundle\Domain\Model\MessageStatus;
use AlexandreBulete\DddContactBundle\Domain\ValueObject\ContactMessageId;

/**
 * Marks a message read, new again, or archived.
 *
 * @implements CommandInterface<ContactMessage>
 */
final readonly class FileContactMessageCommand implements CommandInterface
{
    public function __construct(
        public ContactMessageId $id,
        public MessageStatus $status,
    ) {}
}
