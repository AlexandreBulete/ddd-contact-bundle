<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * Loaded only when alexandrebulete/ddd-notification-bundle is registered: a
 * notice on `contact.message_received` for each message kept.
 */
return static function (ContainerConfigurator $container): void {
    $src = dirname(__DIR__) . '/src';
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('AlexandreBulete\\DddContactBundle\\Infrastructure\\Notification\\', $src . '/Infrastructure/Notification/');
};
