<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Application\Command\DeleteContactMessage;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddContactBundle\Domain\ValueObject\ContactMessageId;

/**
 * Deletes a message for good.
 *
 * @implements CommandInterface<void>
 */
final readonly class DeleteContactMessageCommand implements CommandInterface
{
    public function __construct(
        public ContactMessageId $id,
    ) {}
}
