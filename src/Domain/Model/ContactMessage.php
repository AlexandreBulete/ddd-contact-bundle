<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Domain\Model;

use AlexandreBulete\DddContactBundle\Domain\Event\ContactMessageDeleted;
use AlexandreBulete\DddContactBundle\Domain\Event\ContactMessageFiled;
use AlexandreBulete\DddContactBundle\Domain\Event\ContactMessageFlagged;
use AlexandreBulete\DddContactBundle\Domain\Event\ContactMessageReceived;
use AlexandreBulete\DddContactBundle\Domain\ValueObject\ContactMessageId;
use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;

/**
 * What a visitor wrote through the contact form, kept until someone reads,
 * archives or deletes it. Its content never changes: it is what was sent.
 *
 * A message judged suspicious on arrival is kept, flagged as spam, rather
 * than dropped: a false positive can still be read.
 */
final class ContactMessage
{
    use RecordsEvents;

    public const int NAME_MAX_LENGTH = 120;
    public const int EMAIL_MAX_LENGTH = 180;
    public const int SUBJECT_MAX_LENGTH = 150;
    public const int BODY_MAX_LENGTH = 5000;

    private function __construct(
        public private(set) ContactMessageId $id,
        public private(set) string $senderName,
        public private(set) string $senderEmail,
        public private(set) ?string $subject,
        public private(set) string $body,
        public private(set) MessageStatus $status,
        public private(set) bool $spam,
        public private(set) \DateTimeImmutable $receivedAt,
        public private(set) ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function receive(
        ContactMessageId $id,
        string $senderName,
        string $senderEmail,
        ?string $subject,
        string $body,
        bool $spam,
        \DateTimeImmutable $at,
    ): self {
        $message = new self(
            $id,
            self::text($senderName, 'sender name', self::NAME_MAX_LENGTH),
            self::email($senderEmail),
            self::optionalText($subject, self::SUBJECT_MAX_LENGTH),
            self::text($body, 'message', self::BODY_MAX_LENGTH),
            MessageStatus::New,
            $spam,
            $at,
            null,
        );
        $message->recordEvent(new ContactMessageReceived((string) $id, $spam));

        return $message;
    }

    public function file(MessageStatus $status, \DateTimeImmutable $at): void
    {
        if ($status === $this->status) {
            return;
        }

        $this->status = $status;
        $this->updatedAt = $at;
        $this->recordEvent(new ContactMessageFiled((string) $this->id, $status->value));
    }

    public function flag(bool $spam, \DateTimeImmutable $at): void
    {
        if ($spam === $this->spam) {
            return;
        }

        $this->spam = $spam;
        $this->updatedAt = $at;
        $this->recordEvent(new ContactMessageFlagged((string) $this->id, $spam));
    }

    /**
     * Records the deletion; the repository deletes.
     */
    public function delete(): void
    {
        $this->recordEvent(new ContactMessageDeleted((string) $this->id));
    }

    public function isNew(): bool
    {
        return $this->status === MessageStatus::New;
    }

    private static function text(string $value, string $what, int $max): string
    {
        $value = trim($value);
        if ($value === '' || mb_strlen($value) > $max) {
            throw new \InvalidArgumentException(sprintf('A contact message %s is between 1 and %d characters.', $what, $max));
        }

        return $value;
    }

    private static function optionalText(?string $value, int $max): ?string
    {
        $value = $value === null ? null : trim($value);
        if ($value === null || $value === '') {
            return null;
        }
        if (mb_strlen($value) > $max) {
            throw new \InvalidArgumentException(sprintf('A contact message subject is %d characters at most.', $max));
        }

        return $value;
    }

    private static function email(string $value): string
    {
        $value = trim($value);
        if (mb_strlen($value) > self::EMAIL_MAX_LENGTH || filter_var($value, \FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('A contact message needs a valid sender email address.');
        }

        return $value;
    }
}
