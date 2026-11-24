<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

/**
 * Gone for good.
 */
final readonly class ContactMessageDeleted implements DomainEvent
{
    public function __construct(
        public string $messageId,
    ) {}
}
