<?php

declare(strict_types=1);

use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Grid\ContactMessageGrid;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

/**
 * Loaded only when `contact.admin.enabled` is true: everything Sylius, nothing
 * else. Resources are DTOs, not services.
 */
return static function (ContainerConfigurator $container): void {
    $src = dirname(__DIR__) . '/src';
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('AlexandreBulete\\DddContactBundle\\Infrastructure\\Sylius\\', $src . '/Infrastructure/Sylius/')
        ->exclude([$src . '/Infrastructure/Sylius/Resource/ContactMessageResource.php', $src . '/Infrastructure/Sylius/Resource/ContactSettingsResource.php']);
    $services->load('AlexandreBulete\\DddContactBundle\\Infrastructure\\Symfony\\Form\\Admin\\', $src . '/Infrastructure/Symfony/Form/Admin/');

    $services->set(ContactMessageGrid::class)->args([param('contact.admin.grid_limits')]);
};
