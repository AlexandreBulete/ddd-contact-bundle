<?php

declare(strict_types=1);

use AlexandreBulete\DddContactBundle\Domain\Repository\ContactMessageRepositoryInterface;
use AlexandreBulete\DddContactBundle\Domain\Repository\ContactSettingsRepositoryInterface;
use AlexandreBulete\DddContactBundle\Domain\Service\DomainEventPublisherInterface;
use AlexandreBulete\DddContactBundle\Infrastructure\Doctrine\DoctrineContactMessageRepository;
use AlexandreBulete\DddContactBundle\Infrastructure\Doctrine\DoctrineContactSettingsRepository;
use AlexandreBulete\DddContactBundle\Infrastructure\Doctrine\ImmediateEventPublisher;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $src = dirname(__DIR__) . '/src';
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('AlexandreBulete\\DddContactBundle\\', $src . '/')
        ->exclude([
            // Aggregates and value objects are never services.
            $src . '/Domain',
            $src . '/DddContactBundle.php',
            // Doctrine instantiates types itself.
            $src . '/Infrastructure/Doctrine/Type',
            $src . '/Infrastructure/Doctrine/Mapping',
            // Conditional: services_migrations.php, services_admin.php,
            // services_notification.php, and reCAPTCHA (DddContactBundle).
            $src . '/Infrastructure/Doctrine/Migrations',
            $src . '/Infrastructure/Sylius',
            $src . '/Infrastructure/Symfony/Form/Admin',
            $src . '/Infrastructure/Notification',
            $src . '/Infrastructure/Spam/RecaptchaVerifier.php',
            // Data, not services.
            $src . '/Infrastructure/Spam/Submission.php',
            $src . '/Infrastructure/Spam/Verdict.php',
            $src . '/Infrastructure/Symfony/ContactOutcome.php',
            $src . '/Infrastructure/Symfony/Form/ContactFormData.php',
        ]);

    // ── Ports → adapters ────────────────────────────────────────────────────
    $services->alias(ContactMessageRepositoryInterface::class, DoctrineContactMessageRepository::class);
    $services->alias(ContactSettingsRepositoryInterface::class, DoctrineContactSettingsRepository::class);
    $services->alias(DomainEventPublisherInterface::class, ImmediateEventPublisher::class);
};
