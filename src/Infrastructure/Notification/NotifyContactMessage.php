<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Notification;

use AlexandreBulete\DddContactBundle\Domain\Event\ContactMessageReceived;
use AlexandreBulete\DddNotificationBundle\Application\Port\NotifierInterface;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Notification;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Topic;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Tells whoever subscribed to `contact.message_received` that a message came
 * in, on their channel (email, Slack, SMS…). Loaded only when
 * alexandrebulete/ddd-notification-bundle is registered.
 *
 * The notice says that a message arrived and where to read it — never who
 * wrote or what: a chat or an SMS gateway is a third party, a visitor's
 * words stay in the back office.
 */
#[AsEventListener]
final readonly class NotifyContactMessage
{
    public const string TOPIC = 'contact.message_received';

    public function __construct(
        private NotifierInterface $notifier,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urls,
    ) {}

    public function __invoke(ContactMessageReceived $event): void
    {
        if ($event->spam) {
            return;
        }

        try {
            $link = $this->urls->generate('contact_admin_message_update', ['id' => $event->messageId], UrlGeneratorInterface::ABSOLUTE_URL);
            $body = $this->translator->trans('contact.notification.body_with_link', ['%url%' => $link]);
        } catch (RoutingException) {
            // Back office off: nothing to link to.
            $body = $this->translator->trans('contact.notification.body');
        }

        $this->notifier->notify(new Notification(
            new Topic(self::TOPIC),
            $this->translator->trans('contact.notification.subject'),
            $body,
        ));
    }
}
