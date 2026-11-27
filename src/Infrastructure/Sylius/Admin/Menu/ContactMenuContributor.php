<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Admin\Menu;

use AlexandreBulete\DddContactBundle\Application\Query\CountNewContactMessages\CountNewContactMessagesQuery;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactSettings;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use AlexandreBulete\DddSyliusBundle\Admin\Menu\MenuContributorInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * "Contact": the messages, with how many wait unread, and the settings.
 */
#[AutoconfigureTag('app.menu_contributor', ['priority' => 15])]
final readonly class ContactMenuContributor implements MenuContributorInterface
{
    public function __construct(
        private AuthorizationCheckerInterface $authorization,
        private QueryBusInterface $queryBus,
    ) {}

    public function contribute(ItemInterface $menu): void
    {
        $messages = $this->authorization->isGranted('contact.find_contact_messages');
        $settings = $this->authorization->isGranted('contact.change_contact_settings');
        if (!$messages && !$settings) {
            return;
        }

        $root = $menu->addChild('contact')
            ->setLabel('contact.menu.root')
            ->setLabelAttribute('icon', 'tabler:mail');
        if ($messages) {
            $new = 0;
            if ($this->authorization->isGranted('contact.count_new_contact_messages')) {
                /** @var int $new */
                $new = $this->queryBus->ask(new CountNewContactMessagesQuery());
            }
            $root->addChild('contact_messages', ['route' => 'contact_admin_message_index'])
                ->setLabel('contact.menu.messages')
                ->setExtra('badge', $new);
        }
        if ($settings) {
            $root->addChild('contact_settings', ['route' => 'contact_admin_settings_update', 'routeParameters' => ['id' => ContactSettings::KEY]])
                ->setLabel('contact.menu.settings');
        }
    }
}
