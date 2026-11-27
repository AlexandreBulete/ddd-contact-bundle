<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Resource\ContactMessageResource;
use Sylius\Bundle\GridBundle\Builder\Action\DeleteAction;
use Sylius\Bundle\GridBundle\Builder\Action\UpdateAction;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\Field\DateTimeField;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Bundle\GridBundle\Builder\Field\TwigField;
use Sylius\Bundle\GridBundle\Builder\Filter\StringFilter;
use Sylius\Bundle\GridBundle\Grid\AbstractGrid;
use Sylius\Bundle\GridBundle\Grid\ResourceAwareGridInterface;
use Sylius\Component\Grid\Builder\GridBuilderInterface;

class ContactMessageGrid extends AbstractGrid implements ResourceAwareGridInterface
{
    /**
     * @param list<int> $limits
     */
    public function __construct(
        private readonly array $limits,
    ) {}

    public static function getName(): string
    {
        return self::class;
    }

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setProvider(ContactMessageGridProvider::class)
            ->setLimits($this->limits)
            ->orderBy('receivedAt', 'desc')
            ->addFilter(StringFilter::create('senderEmail')->setLabel('contact.message.sender_email'))
            ->addField(DateTimeField::create('receivedAt')->setLabel('contact.message.received_at')->setSortable(true))
            ->addField(StringField::create('senderName')->setLabel('contact.message.sender_name'))
            ->addField(StringField::create('senderEmail')->setLabel('contact.message.sender_email'))
            ->addField(StringField::create('subject')->setLabel('contact.message.subject'))
            ->addField(TwigField::create('status', '@DddSylius/admin/grid/field/status.html.twig')->setLabel('contact.message.status')->setPath('adminStatus'))
            ->addField(TwigField::create('spam', '@SyliusBootstrapAdminUi/shared/grid/field/boolean.html.twig')->setLabel('contact.message.spam')->setSortable(true))
            ->addActionGroup(ItemActionGroup::create(UpdateAction::create()->setLabel('contact.message.open'), DeleteAction::create()))
        ;
    }

    public function getResourceClass(): string
    {
        return ContactMessageResource::class;
    }
}
