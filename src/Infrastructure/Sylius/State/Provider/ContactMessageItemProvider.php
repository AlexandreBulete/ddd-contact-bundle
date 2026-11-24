<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Sylius\State\Provider;

use AlexandreBulete\DddContactBundle\Application\Command\FileContactMessage\FileContactMessageCommand;
use AlexandreBulete\DddContactBundle\Application\Query\FindContactMessage\FindContactMessageQuery;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddContactBundle\Domain\Model\MessageStatus;
use AlexandreBulete\DddContactBundle\Domain\ValueObject\ContactMessageId;
use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Resource\ContactMessageResource;
use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Resource\ContactMessageResourceFactory;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\State\ProviderInterface;

/**
 * Opening a new message marks it read, like a mailbox.
 */
final readonly class ContactMessageItemProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private CommandBusInterface $commandBus,
        private ContactMessageResourceFactory $resources,
    ) {}

    public function provide(Operation $operation, Context $context): ?ContactMessageResource
    {
        $request = $context->get(RequestOption::class)?->request();
        $id = $request?->attributes->getString('id');
        if ($id === null || $id === '') {
            return null;
        }

        $id = ContactMessageId::fromString($id);
        /** @var ContactMessage $message */
        $message = $this->queryBus->ask(new FindContactMessageQuery($id));
        if ($operation instanceof Update && $request->isMethod('GET') && $message->isNew()) {
            /** @var ContactMessage $message */
            $message = $this->commandBus->dispatch(new FileContactMessageCommand($id, MessageStatus::Read));
        }

        return $this->resources->fromModel($message);
    }
}
