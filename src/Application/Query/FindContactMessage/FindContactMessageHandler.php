<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Application\Query\FindContactMessage;

use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddContactBundle\Domain\Repository\ContactMessageRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Handler\QuerySingleHandler;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

/**
 * @extends QuerySingleHandler<ContactMessage>
 */
#[AsQueryHandler]
final readonly class FindContactMessageHandler extends QuerySingleHandler
{
    public function __construct(ContactMessageRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function __invoke(FindContactMessageQuery $query): ContactMessage
    {
        return $this->build($query);
    }
}
