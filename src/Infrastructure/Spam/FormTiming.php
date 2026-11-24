<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Spam;

use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * When the form was shown, signed, carried by a hidden field: a person needs
 * a few seconds to write; a robot posts at once, or replays an old token.
 */
final readonly class FormTiming
{
    public function __construct(
        private ClockInterface $clock,
        #[Autowire(param: 'kernel.secret')]
        private string $secret,
    ) {}

    public function issue(): string
    {
        $at = (string) $this->clock->now()->getTimestamp();

        return $at . '.' . $this->sign($at);
    }

    /**
     * Seconds since the token was issued; null when it is missing or forged.
     */
    public function elapsed(?string $token): ?int
    {
        if ($token === null || preg_match('/^(\d+)\.([a-f0-9]{64})$/', $token, $parts) !== 1) {
            return null;
        }
        if (!hash_equals($this->sign($parts[1]), $parts[2])) {
            return null;
        }

        return $this->clock->now()->getTimestamp() - (int) $parts[1];
    }

    private function sign(string $at): string
    {
        return hash_hmac('sha256', 'contact-form|' . $at, $this->secret);
    }
}
