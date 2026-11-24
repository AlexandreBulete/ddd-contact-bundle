<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Application\Query\FindContactSettings;

use AlexandreBulete\DddContactBundle\Domain\Model\ContactSettings;
use AlexandreBulete\DddContactBundle\Domain\Repository\ContactSettingsRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

#[AsQueryHandler]
final readonly class FindContactSettingsHandler
{
    public function __construct(
        private ContactSettingsRepositoryInterface $settings,
    ) {}

    public function __invoke(FindContactSettingsQuery $query): ContactSettings
    {
        return $this->settings->get();
    }
}
