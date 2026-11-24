<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Resource\ContactSettingsResource;
use Sylius\Bundle\GridBundle\Builder\Action\UpdateAction;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\Field\DateTimeField;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Bundle\GridBundle\Grid\AbstractGrid;
use Sylius\Bundle\GridBundle\Grid\ResourceAwareGridInterface;
use Sylius\Component\Grid\Builder\GridBuilderInterface;

class ContactSettingsGrid extends AbstractGrid implements ResourceAwareGridInterface
{
    public static function getName(): string
    {
        return self::class;
    }

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setProvider(ContactSettingsGridProvider::class)
            ->addField(StringField::create('forwardTo')->setLabel('contact.settings.forward_to'))
            ->addField(DateTimeField::create('updatedAt')->setLabel('sylius.ui.updated_at'))
            ->addActionGroup(ItemActionGroup::create(UpdateAction::create()))
        ;
    }

    public function getResourceClass(): string
    {
        return ContactSettingsResource::class;
    }
}
