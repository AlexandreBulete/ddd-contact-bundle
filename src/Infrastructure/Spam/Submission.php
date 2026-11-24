<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Spam;

/**
 * What the checks look at, apart from the message itself.
 */
final readonly class Submission
{
    public function __construct(
        public ?string $trap,
        public ?string $timingToken,
        public ?string $clientIp,
        public ?string $recaptchaToken = null,
    ) {}
}
