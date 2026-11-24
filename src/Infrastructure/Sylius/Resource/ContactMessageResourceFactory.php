<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Builds resources with their status label translated (a grid string cell
 * prints as is).
 */
final readonly class ContactMessageResourceFactory
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {}

    public function fromModel(ContactMessage $message): ContactMessageResource
    {
        $resource = ContactMessageResource::fromModel($message);
        $resource->statusLabel = $this->translator->trans((string) $resource->statusLabel);

        return $resource;
    }
}
