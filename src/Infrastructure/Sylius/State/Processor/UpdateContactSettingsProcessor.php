<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddContactBundle\Application\Command\ChangeContactSettings\ChangeContactSettingsCommand;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactSettings;
use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Resource\ContactSettingsResource;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Webmozart\Assert\Assert;

final readonly class UpdateContactSettingsProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): ContactSettingsResource
    {
        Assert::isInstanceOf($data, ContactSettingsResource::class);

        /** @var ContactSettings $settings */
        $settings = $this->commandBus->dispatch(new ChangeContactSettingsCommand($data->forwardTo));

        return ContactSettingsResource::fromModel($settings);
    }
}
