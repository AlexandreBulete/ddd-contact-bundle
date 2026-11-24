<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

/**
 * A visitor wrote. `spam`: judged suspicious on arrival, kept but flagged.
 */
final readonly class ContactMessageReceived implements DomainEvent
{
    public function __construct(
        public string $messageId,
        public bool $spam,
    ) {}
}
