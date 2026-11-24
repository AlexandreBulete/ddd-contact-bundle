<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * `contact_recaptcha_site_key()`: the public key when reCAPTCHA is on, null
 * otherwise. Used by `@DddContact/recaptcha.html.twig`.
 */
final class ContactExtension extends AbstractExtension
{
    public function __construct(
        #[Autowire(param: 'contact.spam.recaptcha.site_key')]
        private readonly ?string $recaptchaSiteKey = null,
    ) {}

    public function getFunctions(): array
    {
        return [new TwigFunction('contact_recaptcha_site_key', fn (): ?string => $this->recaptchaSiteKey)];
    }
}
