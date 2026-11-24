<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Application\Query\FindContactMessage;

use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddContactBundle\Domain\ValueObject\ContactMessageId;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * @implements QueryInterface<ContactMessage>
 */
final readonly class FindContactMessageQuery implements QueryInterface
{
    public function __construct(
        public ContactMessageId $id,
    ) {}
}
