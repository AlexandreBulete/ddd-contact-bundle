<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddContactBundle\Domain\Model\MessageStatus;
use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Grid\ContactMessageGrid;
use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\State\Processor\DeleteContactMessageProcessor;
use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\State\Processor\UpdateContactMessageProcessor;
use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\State\Provider\ContactMessageItemProvider;
use AlexandreBulete\DddContactBundle\Infrastructure\Symfony\Form\Admin\ContactMessageType;
use AlexandreBulete\DddSyliusBundle\Admin\View\AdminRecordInterface;
use AlexandreBulete\DddSyliusBundle\Admin\View\StatusBadge;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Delete;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Component\Uid\AbstractUid;

/**
 * A message in the back office: read as sent, then filed or flagged. No
 * creation screen: messages come from the public form only.
 */
#[AsResource(
    alias: 'contact.message',
    section: 'admin',
    formType: ContactMessageType::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    driver: false,
    operations: [
        new Update(provider: ContactMessageItemProvider::class, processor: UpdateContactMessageProcessor::class),
        new Delete(provider: ContactMessageItemProvider::class, processor: DeleteContactMessageProcessor::class),
        new Index(grid: ContactMessageGrid::class),
    ],
)]
final class ContactMessageResource implements ResourceInterface, AdminRecordInterface
{
    public function __construct(
        public ?AbstractUid $id = null,
        // As sent: shown, never edited.
        public ?string $senderName = null,
        public ?string $senderEmail = null,
        public ?string $subject = null,
        public ?string $body = null,
        public ?\DateTimeImmutable $receivedAt = null,
        // Filed by the reader.
        public MessageStatus $status = MessageStatus::New,
        public bool $spam = false,
        public ?string $statusLabel = null,
    ) {}

    public function getId(): ?AbstractUid
    {
        return $this->id;
    }

    public static function fromModel(ContactMessage $message): self
    {
        return new self(
            id: $message->id->value(),
            senderName: $message->senderName,
            senderEmail: $message->senderEmail,
            subject: $message->subject,
            body: $message->body,
            receivedAt: $message->receivedAt,
            status: $message->status,
            spam: $message->spam,
            statusLabel: 'contact.message.status_' . $message->status->value,
        );
    }

    public function adminLabel(): ?string
    {
        return $this->subject !== null && $this->subject !== '' ? $this->subject : $this->senderName;
    }

    public function adminStatus(): StatusBadge
    {
        return $this->spam ? StatusBadge::danger('contact.message.spam') : new StatusBadge($this->statusLabel ?? 'contact.message.status_' . $this->status->value, match ($this->status) {
            MessageStatus::New => StatusBadge::INFO,
            default => StatusBadge::NEUTRAL,
        });
    }

    public function adminPublicUrl(): ?string
    {
        return null;
    }
}
