<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Spam;

/**
 * What the checks make of a submission.
 */
enum Verdict
{
    // Kept as is.
    case Clean;
    // Kept, flagged as spam: a false positive can still be read.
    case Suspicious;
    // A robot for sure (trap field filled, sent too fast): dropped, while the
    // sender is told it went through — nothing to learn from a refusal.
    case Robot;
    // Too many messages from this address lately: refused, said so.
    case TooMany;
}
