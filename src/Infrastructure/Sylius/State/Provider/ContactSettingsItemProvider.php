<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Sylius\State\Provider;

use AlexandreBulete\DddContactBundle\Application\Query\FindContactSettings\FindContactSettingsQuery;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactSettings;
use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Resource\ContactSettingsResource;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProviderInterface;

/**
 * Whatever the id in the URL: there is one set of settings.
 */
final readonly class ContactSettingsItemProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {}

    public function provide(Operation $operation, Context $context): ContactSettingsResource
    {
        /** @var ContactSettings $settings */
        $settings = $this->queryBus->ask(new FindContactSettingsQuery());

        return ContactSettingsResource::fromModel($settings);
    }
}
