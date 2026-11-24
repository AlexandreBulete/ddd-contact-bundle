<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Application\Query\FindContactMessages;

use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddContactBundle\Domain\Repository\ContactMessageRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Handler\QueryCollectionHandler;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;
use AlexandreBulete\DddFoundation\Domain\Repository\RepositoryInterface;

/**
 * @extends QueryCollectionHandler<ContactMessage>
 */
#[AsQueryHandler]
final readonly class FindContactMessagesHandler extends QueryCollectionHandler
{
    public function __construct(ContactMessageRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * @return RepositoryInterface<ContactMessage>
     */
    public function __invoke(FindContactMessagesQuery $query): RepositoryInterface
    {
        return $this->build($query);
    }
}
