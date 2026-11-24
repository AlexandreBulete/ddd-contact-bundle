<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Symfony;

enum ContactOutcome
{
    // Shown, not sent yet.
    case Pending;
    // Sent back with errors to fix.
    case Invalid;
    // Too many messages from this address lately: the form says so.
    case TooMany;
    // Thanked. Kept, flagged as spam, or dropped as a robot's: the sender is
    // told the same, so that a robot learns nothing.
    case Sent;
}
