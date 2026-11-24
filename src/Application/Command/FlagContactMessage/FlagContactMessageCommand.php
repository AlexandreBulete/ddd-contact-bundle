<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Application\Command\FlagContactMessage;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddContactBundle\Domain\ValueObject\ContactMessageId;

/**
 * Flags a message as spam, or clears the flag.
 *
 * @implements CommandInterface<ContactMessage>
 */
final readonly class FlagContactMessageCommand implements CommandInterface
{
    public function __construct(
        public ContactMessageId $id,
        public bool $spam,
    ) {}
}
