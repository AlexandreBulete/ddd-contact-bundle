<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Domain\Model;

use AlexandreBulete\DddContactBundle\Domain\Event\ContactSettingsChanged;
use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;

/**
 * The inbox settings, edited in the back office: one row, under a fixed key.
 *
 * Delivery itself (SMTP, notification channels) is not here: transports and
 * their secrets belong to the environment.
 */
final class ContactSettings
{
    use RecordsEvents;

    public const string KEY = 'default';
    public const int EMAIL_MAX_LENGTH = 180;

    private function __construct(
        public private(set) string $key,
        public private(set) ?string $forwardTo,
        public private(set) ?\DateTimeImmutable $updatedAt,
    ) {}

    /**
     * Until someone saves them: nothing forwarded.
     */
    public static function defaults(): self
    {
        return new self(self::KEY, null, null);
    }

    /**
     * Where a full copy of each message goes by email; null for none.
     */
    public function forwardTo(?string $address, \DateTimeImmutable $at): void
    {
        $address = $address === null || trim($address) === '' ? null : trim($address);
        if ($address !== null && (mb_strlen($address) > self::EMAIL_MAX_LENGTH || filter_var($address, \FILTER_VALIDATE_EMAIL) === false)) {
            throw new \InvalidArgumentException('The forwarding address must be a valid email address.');
        }
        if ($address === $this->forwardTo) {
            return;
        }

        $this->forwardTo = $address;
        $this->updatedAt = $at;
        $this->recordEvent(new ContactSettingsChanged());
    }
}
