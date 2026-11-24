<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Mail;

use AlexandreBulete\DddContactBundle\Domain\Event\ContactMessageReceived;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddContactBundle\Domain\Repository\ContactMessageRepositoryInterface;
use AlexandreBulete\DddContactBundle\Domain\Repository\ContactSettingsRepositoryInterface;
use AlexandreBulete\DddContactBundle\Domain\ValueObject\ContactMessageId;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * A full copy of each message, by email, to the address set in the back
 * office; the sender as Reply-To, to answer from the mailbox. Off until an
 * address is set, and without a `contact.mail.from` sender.
 *
 * Spam is not forwarded. A failed send is logged: the message is kept
 * anyway, in the back office.
 */
#[AsEventListener]
final readonly class ForwardContactMessage
{
    public function __construct(
        private ContactMessageRepositoryInterface $messages,
        private ContactSettingsRepositoryInterface $settings,
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
        #[Autowire(param: 'contact.mail.from')]
        private string $from,
    ) {}

    public function __invoke(ContactMessageReceived $event): void
    {
        $to = $this->settings->get()->forwardTo;
        if ($event->spam || $to === null || $this->from === '') {
            return;
        }
        $message = $this->messages->findById(ContactMessageId::fromString($event->messageId));
        if (!$message instanceof ContactMessage) {
            return;
        }

        $email = (new Email())
            ->from($this->from)
            ->to($to)
            ->replyTo(new Address($message->senderEmail, $message->senderName))
            ->subject($this->translator->trans('contact.mail.subject', ['%subject%' => $message->subject ?? $this->translator->trans('contact.mail.no_subject')]))
            ->text($this->translator->trans('contact.mail.body', [
                '%name%' => $message->senderName,
                '%email%' => $message->senderEmail,
                '%body%' => $message->body,
            ]));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Contact message not forwarded by email.', ['message' => $event->messageId, 'exception' => $e]);
        }
    }
}
