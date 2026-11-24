<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Application\Command\FlagContactMessage;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use Psr\Clock\ClockInterface;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddContactBundle\Domain\Repository\ContactMessageRepositoryInterface;

#[AsCommandHandler]
final readonly class FlagContactMessageHandler
{
    public function __construct(
        private ContactMessageRepositoryInterface $messages,
        private ClockInterface $clock,
    ) {}

    public function __invoke(FlagContactMessageCommand $command): ContactMessage
    {
        $message = $this->messages->findById($command->id)
            ?? throw new EntityNotFoundException(ContactMessage::class, $command->id);

        $message->flag($command->spam, $this->clock->now());
        $this->messages->save($message);

        return $message;
    }
}
