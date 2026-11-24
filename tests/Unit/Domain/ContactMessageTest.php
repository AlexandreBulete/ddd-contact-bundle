<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Tests\Unit\Domain;

use AlexandreBulete\DddContactBundle\Domain\Event\ContactMessageFiled;
use AlexandreBulete\DddContactBundle\Domain\Event\ContactMessageFlagged;
use AlexandreBulete\DddContactBundle\Domain\Event\ContactMessageReceived;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactSettings;
use AlexandreBulete\DddContactBundle\Domain\Model\MessageStatus;
use AlexandreBulete\DddContactBundle\Domain\ValueObject\ContactMessageId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ContactMessageTest extends TestCase
{
    private \DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->now = new \DateTimeImmutable('2026-11-24 10:00:00');
    }

    #[Test]
    public function a_received_message_is_new_and_says_whether_it_is_spam(): void
    {
        $message = ContactMessage::receive(ContactMessageId::generate(), '  Ada ', 'ada@example.test', ' ', 'Bonjour, une question.', true, $this->now);

        self::assertSame('Ada', $message->senderName);
        self::assertNull($message->subject);
        self::assertTrue($message->isNew());
        $events = $message->releaseEvents();
        self::assertInstanceOf(ContactMessageReceived::class, $events[0]);
        self::assertTrue($events[0]->spam);
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function invalid(): iterable
    {
        yield 'no name' => [' ', 'ada@example.test', 'Bonjour.'];
        yield 'bad email' => ['Ada', 'ada@', 'Bonjour.'];
        yield 'empty message' => ['Ada', 'ada@example.test', '  '];
        yield 'message too long' => ['Ada', 'ada@example.test', str_repeat('a', ContactMessage::BODY_MAX_LENGTH + 1)];
    }

    #[Test]
    #[DataProvider('invalid')]
    public function an_incomplete_message_is_refused(string $name, string $email, string $body): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ContactMessage::receive(ContactMessageId::generate(), $name, $email, null, $body, false, $this->now);
    }

    #[Test]
    public function filing_and_flagging_record_only_changes(): void
    {
        $message = ContactMessage::receive(ContactMessageId::generate(), 'Ada', 'ada@example.test', null, 'Bonjour.', false, $this->now);
        $message->releaseEvents();

        $message->file(MessageStatus::Read, $this->now);
        $message->file(MessageStatus::Read, $this->now);
        $message->flag(true, $this->now);
        $message->flag(true, $this->now);

        $events = $message->releaseEvents();
        self::assertCount(2, $events);
        self::assertInstanceOf(ContactMessageFiled::class, $events[0]);
        self::assertInstanceOf(ContactMessageFlagged::class, $events[1]);
        self::assertFalse($message->isNew());
    }

    #[Test]
    public function the_forwarding_address_must_be_an_email_address(): void
    {
        $settings = ContactSettings::defaults();
        $settings->forwardTo(' ', $this->now);
        self::assertNull($settings->forwardTo);
        self::assertSame([], $settings->releaseEvents());

        $settings->forwardTo('inbox@example.test', $this->now);
        self::assertSame('inbox@example.test', $settings->forwardTo);

        $this->expectException(\InvalidArgumentException::class);
        $settings->forwardTo('not an address', $this->now);
    }
}
