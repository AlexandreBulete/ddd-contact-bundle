<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Application\Command\ReceiveContactMessage;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use Psr\Clock\ClockInterface;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddContactBundle\Domain\Repository\ContactMessageRepositoryInterface;

#[AsCommandHandler]
final readonly class ReceiveContactMessageHandler
{
    public function __construct(
        private ContactMessageRepositoryInterface $messages,
        private ClockInterface $clock,
    ) {}

    public function __invoke(ReceiveContactMessageCommand $command): ContactMessage
    {
        $message = ContactMessage::receive(
            $command->id,
            $command->senderName,
            $command->senderEmail,
            $command->subject,
            $command->body,
            $command->spam,
            $this->clock->now(),
        );
        $this->messages->save($message);

        return $message;
    }
}
