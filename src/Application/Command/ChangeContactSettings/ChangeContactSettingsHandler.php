<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Application\Command\ChangeContactSettings;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use Psr\Clock\ClockInterface;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactSettings;
use AlexandreBulete\DddContactBundle\Domain\Repository\ContactSettingsRepositoryInterface;

#[AsCommandHandler]
final readonly class ChangeContactSettingsHandler
{
    public function __construct(
        private ContactSettingsRepositoryInterface $settings,
        private ClockInterface $clock,
    ) {}

    public function __invoke(ChangeContactSettingsCommand $command): ContactSettings
    {
        $settings = $this->settings->get();
        $settings->forwardTo($command->forwardTo, $this->clock->now());
        $this->settings->save($settings);

        return $settings;
    }
}
