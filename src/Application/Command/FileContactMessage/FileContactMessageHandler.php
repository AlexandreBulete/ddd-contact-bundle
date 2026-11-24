<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Application\Command\FileContactMessage;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use Psr\Clock\ClockInterface;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddContactBundle\Domain\Repository\ContactMessageRepositoryInterface;

#[AsCommandHandler]
final readonly class FileContactMessageHandler
{
    public function __construct(
        private ContactMessageRepositoryInterface $messages,
        private ClockInterface $clock,
    ) {}

    public function __invoke(FileContactMessageCommand $command): ContactMessage
    {
        $message = $this->messages->findById($command->id)
            ?? throw new EntityNotFoundException(ContactMessage::class, $command->id);

        $message->file($command->status, $this->clock->now());
        $this->messages->save($message);

        return $message;
    }
}
