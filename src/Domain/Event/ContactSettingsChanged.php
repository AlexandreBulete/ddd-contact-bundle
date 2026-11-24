<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

/**
 * The inbox settings were saved with a change.
 */
final readonly class ContactSettingsChanged implements DomainEvent
{
    public function __construct(
    ) {}
}
