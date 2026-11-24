<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Spam;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Google reCAPTCHA v3, invisible: the page asks Google for a token, the
 * server asks Google how human it scored. Opt-in (contact.spam.recaptcha).
 */
final readonly class RecaptchaVerifier
{
    public const string ACTION = 'contact';
    private const string ENDPOINT = 'https://www.google.com/recaptcha/api/siteverify';

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $secret,
        private float $minScore,
    ) {}

    /**
     * Whether the token passes. A token missing or refused, a score too low,
     * or Google out of reach: no. The caller keeps the message as suspicious
     * rather than losing it.
     */
    public function passes(?string $token, ?string $clientIp): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        try {
            $result = $this->httpClient->request('POST', self::ENDPOINT, [
                'body' => array_filter(['secret' => $this->secret, 'response' => $token, 'remoteip' => $clientIp], static fn (?string $value): bool => $value !== null && $value !== ''),
                'timeout' => 5,
            ])->toArray();
        } catch (ExceptionInterface $e) {
            $this->logger->warning('reCAPTCHA unreachable, message kept as suspicious.', ['exception' => $e]);

            return false;
        }

        $score = $result['score'] ?? null;

        return ($result['success'] ?? false) === true
            && ($result['action'] ?? null) === self::ACTION
            && (is_float($score) || is_int($score))
            && $score >= $this->minScore;
    }
}
