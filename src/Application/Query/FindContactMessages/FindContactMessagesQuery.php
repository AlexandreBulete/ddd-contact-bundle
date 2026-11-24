<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Application\Query\FindContactMessages;

use AlexandreBulete\DddContactBundle\Domain\Repository\ContactMessageRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * The inbox, for the back office: filtered, sorted, paginated.
 *
 * @implements QueryInterface<ContactMessageRepositoryInterface>
 */
final readonly class FindContactMessagesQuery implements QueryInterface
{
    /**
     * @param array<string, mixed>  $criteria
     * @param array<string, string> $withSorting
     */
    public function __construct(
        public ?int $page = null,
        public ?int $itemsPerPage = null,
        public array $criteria = [],
        public array $withSorting = [],
    ) {}
}
