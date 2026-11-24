<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

/**
 * Read, back to new, or archived.
 */
final readonly class ContactMessageFiled implements DomainEvent
{
    public function __construct(
        public string $messageId,
        public string $status,
    ) {}
}
