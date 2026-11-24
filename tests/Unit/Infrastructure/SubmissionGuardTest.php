<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Tests\Unit\Infrastructure;

use AlexandreBulete\DddContactBundle\Infrastructure\Spam\FormTiming;
use AlexandreBulete\DddContactBundle\Infrastructure\Spam\RecaptchaVerifier;
use AlexandreBulete\DddContactBundle\Infrastructure\Spam\Submission;
use AlexandreBulete\DddContactBundle\Infrastructure\Spam\SubmissionGuard;
use AlexandreBulete\DddContactBundle\Infrastructure\Spam\Verdict;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

final class SubmissionGuardTest extends TestCase
{
    private MockClock $clock;
    private FormTiming $timing;

    protected function setUp(): void
    {
        $this->clock = new MockClock('2026-11-24 10:00:00');
        $this->timing = new FormTiming($this->clock, 'test-secret');
    }

    #[Test]
    public function a_person_writing_for_a_while_passes(): void
    {
        $token = $this->timing->issue();
        $this->clock->sleep(40);

        self::assertSame(Verdict::Clean, $this->guard()->judge(new Submission(null, $token, '192.0.2.1')));
    }

    #[Test]
    public function a_filled_trap_a_hasty_or_forged_form_are_robots(): void
    {
        $token = $this->timing->issue();
        $guard = $this->guard();

        self::assertSame(Verdict::Robot, $guard->judge(new Submission(null, $token, '192.0.2.1')), 'sent at once');
        $this->clock->sleep(40);
        self::assertSame(Verdict::Robot, $guard->judge(new Submission('https://spam.example', $token, '192.0.2.1')), 'trap filled');
        self::assertSame(Verdict::Robot, $guard->judge(new Submission(null, substr($token, 0, -1) . (str_ends_with($token, '0') ? '1' : '0'), '192.0.2.1')), 'forged');
        self::assertSame(Verdict::Robot, $guard->judge(new Submission(null, null, '192.0.2.1')), 'no token');
        $this->clock->sleep(86400);
        self::assertSame(Verdict::Robot, $guard->judge(new Submission(null, $token, '192.0.2.1')), 'replayed a day later');
    }

    #[Test]
    public function an_address_sending_too_often_is_held_back(): void
    {
        $token = $this->timing->issue();
        $this->clock->sleep(40);
        $guard = $this->guard(limit: 2);

        $verdicts = [];
        foreach (range(1, 3) as $attempt) {
            $verdicts[] = $guard->judge(new Submission(null, $token, '192.0.2.1'));
        }
        self::assertSame([Verdict::Clean, Verdict::Clean, Verdict::TooMany], $verdicts);
        self::assertSame(Verdict::TooMany, $guard->judge(new Submission(null, $token, '192.0.2.1')));
        self::assertSame(Verdict::Clean, $guard->judge(new Submission(null, $token, '198.51.100.7')), 'another address');
    }

    #[Test]
    public function a_low_recaptcha_score_makes_it_suspicious(): void
    {
        $token = $this->timing->issue();
        $this->clock->sleep(40);
        $answers = [
            new JsonMockResponse(['success' => true, 'score' => 0.9, 'action' => 'contact']),
            new JsonMockResponse(['success' => true, 'score' => 0.1, 'action' => 'contact']),
            new JsonMockResponse(['success' => true, 'score' => 0.9, 'action' => 'login']),
        ];
        $recaptcha = new RecaptchaVerifier(new MockHttpClient($answers), new NullLogger(), 'secret', 0.5);
        $guard = $this->guard(recaptcha: $recaptcha);

        self::assertSame(Verdict::Clean, $guard->judge(new Submission(null, $token, '192.0.2.1', 'token')));
        self::assertSame(Verdict::Suspicious, $guard->judge(new Submission(null, $token, '192.0.2.1', 'token')));
        self::assertSame(Verdict::Suspicious, $guard->judge(new Submission(null, $token, '192.0.2.1', 'token')), 'other action');
        self::assertSame(Verdict::Suspicious, $guard->judge(new Submission(null, $token, '192.0.2.1')), 'no token');
    }

    private function guard(int $limit = 5, ?RecaptchaVerifier $recaptcha = null): SubmissionGuard
    {
        $limiter = new RateLimiterFactory(['id' => 'contact', 'policy' => 'sliding_window', 'limit' => $limit, 'interval' => '1 hour'], new InMemoryStorage());

        return new SubmissionGuard($this->timing, $limiter, 3, 86400, $recaptcha);
    }
}
