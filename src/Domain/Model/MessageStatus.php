<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Domain\Model;

/**
 * Where a message stands in the inbox. Spam is not a status: a message is
 * flagged as spam whatever its status, and unflagged without losing it.
 */
enum MessageStatus: string
{
    case New = 'new';
    case Read = 'read';
    case Archived = 'archived';
}
