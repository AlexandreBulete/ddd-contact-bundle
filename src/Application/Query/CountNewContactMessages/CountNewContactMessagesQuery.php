<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Application\Query\CountNewContactMessages;

use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * How many messages wait to be read (spam left out): the menu badge.
 *
 * @implements QueryInterface<int>
 */
final readonly class CountNewContactMessagesQuery implements QueryInterface
{
}
