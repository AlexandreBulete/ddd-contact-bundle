<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Application\Command\DeleteContactMessage;

use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddContactBundle\Domain\Repository\ContactMessageRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;

#[AsCommandHandler]
final readonly class DeleteContactMessageHandler
{
    public function __construct(
        private ContactMessageRepositoryInterface $messages,
    ) {}

    public function __invoke(DeleteContactMessageCommand $command): void
    {
        $message = $this->messages->findById($command->id)
            ?? throw new EntityNotFoundException(ContactMessage::class, $command->id);

        $message->delete();
        $this->messages->remove($message);
    }
}
