<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Application\Query\CountNewContactMessages;

use AlexandreBulete\DddContactBundle\Domain\Repository\ContactMessageRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

#[AsQueryHandler]
final readonly class CountNewContactMessagesHandler
{
    public function __construct(
        private ContactMessageRepositoryInterface $messages,
    ) {}

    public function __invoke(CountNewContactMessagesQuery $query): int
    {
        return $this->messages->countNew();
    }
}
