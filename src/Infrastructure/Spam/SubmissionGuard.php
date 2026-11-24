<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Spam;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

/**
 * The checks a submission goes through, cheapest first. None needs a third
 * party by default; reCAPTCHA only adds to them when enabled.
 */
final readonly class SubmissionGuard
{
    public function __construct(
        private FormTiming $timing,
        #[Autowire(service: 'limiter.contact_form')]
        private RateLimiterFactoryInterface $limiter,
        #[Autowire(param: 'contact.spam.min_seconds')]
        private int $minSeconds,
        #[Autowire(param: 'contact.spam.max_age')]
        private int $maxAge,
        private ?RecaptchaVerifier $recaptcha = null,
    ) {}

    public function judge(Submission $submission): Verdict
    {
        if ($submission->trap !== null && $submission->trap !== '') {
            return Verdict::Robot;
        }
        $elapsed = $this->timing->elapsed($submission->timingToken);
        if ($elapsed === null || $elapsed < $this->minSeconds || $elapsed > $this->maxAge) {
            return Verdict::Robot;
        }
        if (!$this->limiter->create($submission->clientIp ?? 'unknown')->consume()->isAccepted()) {
            return Verdict::TooMany;
        }
        if ($this->recaptcha !== null && !$this->recaptcha->passes($submission->recaptchaToken, $submission->clientIp)) {
            return Verdict::Suspicious;
        }

        return Verdict::Clean;
    }
}
