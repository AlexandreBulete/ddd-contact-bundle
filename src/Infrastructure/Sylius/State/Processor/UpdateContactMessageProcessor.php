<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddContactBundle\Application\Command\FileContactMessage\FileContactMessageCommand;
use AlexandreBulete\DddContactBundle\Application\Command\FlagContactMessage\FlagContactMessageCommand;
use AlexandreBulete\DddContactBundle\Application\Query\FindContactMessage\FindContactMessageQuery;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddContactBundle\Domain\ValueObject\ContactMessageId;
use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Resource\ContactMessageResource;
use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Resource\ContactMessageResourceFactory;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

/**
 * Only what the reader changed becomes a command.
 */
final readonly class UpdateContactMessageProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
        private ContactMessageResourceFactory $resources,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): ContactMessageResource
    {
        Assert::isInstanceOf($data, ContactMessageResource::class);
        Assert::isInstanceOf($data->id, Ulid::class);

        $id = ContactMessageId::fromUlid($data->id);
        /** @var ContactMessage $message */
        $message = $this->queryBus->ask(new FindContactMessageQuery($id));
        if ($data->status !== $message->status) {
            /** @var ContactMessage $message */
            $message = $this->commandBus->dispatch(new FileContactMessageCommand($id, $data->status));
        }
        if ($data->spam !== $message->spam) {
            /** @var ContactMessage $message */
            $message = $this->commandBus->dispatch(new FlagContactMessageCommand($id, $data->spam));
        }

        return $this->resources->fromModel($message);
    }
}
