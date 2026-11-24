<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Tests\Integration;

use AlexandreBulete\DddContactBundle\Application\Command\ChangeContactSettings\ChangeContactSettingsCommand;
use AlexandreBulete\DddContactBundle\Application\Command\ChangeContactSettings\ChangeContactSettingsHandler;
use AlexandreBulete\DddContactBundle\Application\Command\FileContactMessage\FileContactMessageCommand;
use AlexandreBulete\DddContactBundle\Application\Command\FileContactMessage\FileContactMessageHandler;
use AlexandreBulete\DddContactBundle\Application\Command\ReceiveContactMessage\ReceiveContactMessageCommand;
use AlexandreBulete\DddContactBundle\Application\Command\ReceiveContactMessage\ReceiveContactMessageHandler;
use AlexandreBulete\DddContactBundle\Domain\Model\MessageStatus;
use AlexandreBulete\DddContactBundle\Domain\ValueObject\ContactMessageId;
use AlexandreBulete\DddContactBundle\Infrastructure\Doctrine\DoctrineContactMessageRepository;
use AlexandreBulete\DddContactBundle\Infrastructure\Doctrine\DoctrineContactSettingsRepository;
use AlexandreBulete\DddContactBundle\Tests\Integration\Fixture\NullEventPublisher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class InboxTest extends TestCase
{
    private EntityManagerInterface $em;
    private DoctrineContactMessageRepository $messages;
    private DoctrineContactSettingsRepository $settings;
    private MockClock $clock;

    protected function setUp(): void
    {
        $this->em = ContactDatabase::migrated();
        $this->messages = new DoctrineContactMessageRepository($this->em, new NullEventPublisher());
        $this->settings = new DoctrineContactSettingsRepository($this->em, new NullEventPublisher());
        $this->clock = new MockClock('2026-11-24 10:00:00');
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->close();
    }

    #[Test]
    public function the_new_messages_count_leaves_out_read_ones_and_spam(): void
    {
        $first = $this->receive('Ada');
        $this->receive('Grace');
        $this->receive('Robot', spam: true);
        self::assertSame(2, $this->messages->countNew());

        (new FileContactMessageHandler($this->messages, $this->clock))(new FileContactMessageCommand($first, MessageStatus::Read));
        $this->em->clear();

        self::assertSame(1, $this->messages->countNew());
        self::assertSame(MessageStatus::Read, $this->messages->findById($first)?->status);
    }

    #[Test]
    public function the_inbox_lists_the_latest_first(): void
    {
        $this->receive('Ada');
        $this->clock->sleep(60);
        $this->receive('Grace');
        $this->em->clear();

        $names = [];
        foreach ($this->messages->orderBy('receivedAt', 'desc') as $message) {
            $names[] = $message->senderName;
        }
        self::assertSame(['Grace', 'Ada'], $names);
    }

    #[Test]
    public function settings_start_empty_then_keep_what_was_saved(): void
    {
        self::assertNull($this->settings->get()->forwardTo);

        (new ChangeContactSettingsHandler($this->settings, $this->clock))(new ChangeContactSettingsCommand('inbox@example.test'));
        $this->em->clear();

        self::assertSame('inbox@example.test', $this->settings->get()->forwardTo);
    }

    private function receive(string $name, bool $spam = false): ContactMessageId
    {
        $id = ContactMessageId::generate();
        (new ReceiveContactMessageHandler($this->messages, $this->clock))(
            new ReceiveContactMessageCommand($id, $name, strtolower($name) . '@example.test', null, 'Bonjour, une question.', $spam),
        );

        return $id;
    }
}
