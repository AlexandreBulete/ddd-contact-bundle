<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle;

use AlexandreBulete\DddContactBundle\Infrastructure\Doctrine\Type\ContactMessageIdType;
use AlexandreBulete\DddContactBundle\Infrastructure\Notification\NotifyContactMessage;
use AlexandreBulete\DddContactBundle\Infrastructure\Spam\RecaptchaVerifier;
use AlexandreBulete\DddNotificationBundle\DddNotificationBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * A contact form whose messages are kept and read in the back office.
 *
 * Delivery is the environment's business: SMTP through Symfony Mailer
 * (MAILER_DSN), notices through alexandrebulete/ddd-notification-bundle
 * when installed. Secrets (reCAPTCHA) come from env variables.
 *
 * @phpstan-type ContactConfig array{
 *     mail: array{from: string},
 *     spam: array{
 *         min_seconds: int,
 *         max_age: int,
 *         rate_limit: array{limit: int, interval: string},
 *         recaptcha: array{enabled: bool, site_key: string, secret: string, min_score: float},
 *     },
 *     admin: array{enabled: bool, grid_limits: list<int>},
 * }
 */
final class DddContactBundle extends AbstractBundle
{
    public const string RATE_LIMITER = 'contact_form';

    protected string $extensionAlias = 'contact';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('mail')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('from')
                            ->defaultValue('')
                            ->info('Sender of the copies forwarded by email, on a domain the SMTP relay may send for. Empty: no forwarding, whatever the back office says.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('spam')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('min_seconds')
                            ->defaultValue(3)->min(0)
                            ->info('A form sent faster than this after being shown is a robot\'s.')
                        ->end()
                        ->integerNode('max_age')
                            ->defaultValue(86400)->min(60)
                            ->info('A form older than this (seconds) is refused as a replay.')
                        ->end()
                        ->arrayNode('rate_limit')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('limit')->defaultValue(5)->min(1)->end()
                                ->scalarNode('interval')->defaultValue('1 hour')->end()
                            ->end()
                        ->end()
                        ->arrayNode('recaptcha')
                            ->canBeEnabled()
                            ->info('Google reCAPTCHA v3, invisible. Adds to the default checks, never replaces them. Loads a Google script: mention it in the privacy policy.')
                            ->children()
                                ->scalarNode('site_key')->defaultValue('')->end()
                                ->scalarNode('secret')->defaultValue('')->end()
                                ->floatNode('min_score')->defaultValue(0.5)->min(0.0)->max(1.0)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Sylius back-office screens. Turn off for a headless deployment.')
                        ->end()
                        ->arrayNode('grid_limits')
                            ->integerPrototype()->end()
                            ->defaultValue([20, 50, 100])
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array<mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        /** @var ContactConfig $config */
        $recaptcha = $config['spam']['recaptcha'];
        $container->parameters()
            ->set('contact.mail.from', $config['mail']['from'])
            ->set('contact.spam.min_seconds', $config['spam']['min_seconds'])
            ->set('contact.spam.max_age', $config['spam']['max_age'])
            ->set('contact.spam.recaptcha.site_key', $recaptcha['enabled'] ? $recaptcha['site_key'] : null)
            ->set('contact.admin.grid_limits', $config['admin']['grid_limits']);

        $container->import($this->getPath() . '/config/services.php');
        if ($recaptcha['enabled']) {
            $container->services()->set(RecaptchaVerifier::class)
                ->autowire()
                ->arg('$secret', $recaptcha['secret'])
                ->arg('$minScore', $recaptcha['min_score']);
        }
        if ($config['admin']['enabled']) {
            $container->import($this->getPath() . '/config/services_admin.php');
        }
        if (self::hasBundle($builder, DoctrineMigrationsBundle::class)) {
            $container->import($this->getPath() . '/config/services_migrations.php');
        }
        if (self::hasBundle($builder, DddNotificationBundle::class)) {
            $container->import($this->getPath() . '/config/services_notification.php');
        }
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Before the config tree is processed: defaults are not applied yet.
        $adminEnabled = true;
        $rateLimit = ['limit' => 5, 'interval' => '1 hour'];
        /** @var list<array<string, mixed>> $configs */
        $configs = $builder->getExtensionConfig($this->extensionAlias);
        foreach ($configs as $config) {
            $admin = $config['admin'] ?? null;
            if (is_array($admin) && is_bool($admin['enabled'] ?? null)) {
                $adminEnabled = $admin['enabled'];
            }
            $spam = $config['spam'] ?? null;
            if (is_array($spam) && is_array($spam['rate_limit'] ?? null)) {
                $rateLimit = array_merge($rateLimit, array_intersect_key($spam['rate_limit'], $rateLimit));
            }
        }

        $builder->prependExtensionConfig('doctrine', [
            'dbal' => ['types' => [ContactMessageIdType::NAME => ContactMessageIdType::class]],
            'orm' => [
                'mappings' => [
                    'Contact' => [
                        'type' => 'xml',
                        'is_bundle' => false,
                        'dir' => $this->getPath() . '/src/Infrastructure/Doctrine/Mapping',
                        'prefix' => 'AlexandreBulete\DddContactBundle\Domain\Model',
                        'alias' => 'Contact',
                    ],
                ],
            ],
        ]);

        if (self::hasBundle($builder, DoctrineMigrationsBundle::class)) {
            $builder->prependExtensionConfig('doctrine_migrations', ['enable_service_migrations' => true]);
        }

        $builder->prependExtensionConfig('framework', [
            'translator' => ['paths' => [$this->getPath() . '/translations']],
            // Per client address, in the application cache.
            'rate_limiter' => [
                self::RATE_LIMITER => ['policy' => 'sliding_window'] + $rateLimit,
            ],
        ]);

        if (self::hasBundle($builder, DddNotificationBundle::class)) {
            $builder->prependExtensionConfig('notification', [
                'topics' => [NotifyContactMessage::TOPIC => 'Contact : message reçu'],
            ]);
        }

        if ($adminEnabled) {
            // DddSyliusBundle only globs the application's own resources.
            $builder->prependExtensionConfig('sylius_resource', [
                'mapping' => ['paths' => [$this->getPath() . '/src/Infrastructure/Sylius/Resource']],
            ]);
        }
    }

    /**
     * From `kernel.bundles`: inside loadExtension() the builder is scoped to
     * this extension, so hasExtension() would answer false.
     *
     * @param class-string $bundle
     */
    private static function hasBundle(ContainerBuilder $builder, string $bundle): bool
    {
        /** @var array<string, class-string> $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return in_array($bundle, $bundles, true);
    }
}
