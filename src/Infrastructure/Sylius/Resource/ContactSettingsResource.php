<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddContactBundle\Domain\Model\ContactSettings;
use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Grid\ContactSettingsGrid;
use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\State\Processor\UpdateContactSettingsProcessor;
use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\State\Provider\ContactSettingsItemProvider;
use AlexandreBulete\DddContactBundle\Infrastructure\Symfony\Form\Admin\ContactSettingsType;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The inbox settings: one row. The index lists it alone, so that the
 * back-office links and breadcrumbs have somewhere to go.
 */
#[AsResource(
    alias: 'contact.settings',
    section: 'admin',
    formType: ContactSettingsType::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    name: 'settings',
    pluralName: 'settings',
    driver: false,
    operations: [
        new Update(provider: ContactSettingsItemProvider::class, processor: UpdateContactSettingsProcessor::class),
        new Index(grid: ContactSettingsGrid::class),
    ],
)]
final class ContactSettingsResource implements ResourceInterface
{
    public function __construct(
        public string $id = ContactSettings::KEY,
        #[Assert\Email]
        #[Assert\Length(max: ContactSettings::EMAIL_MAX_LENGTH)]
        public ?string $forwardTo = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public static function fromModel(ContactSettings $settings): self
    {
        return new self($settings->key, $settings->forwardTo, $settings->updatedAt);
    }
}
