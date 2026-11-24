<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

/**
 * Flagged as spam, or cleared.
 */
final readonly class ContactMessageFlagged implements DomainEvent
{
    public function __construct(
        public string $messageId,
        public bool $spam,
    ) {}
}
