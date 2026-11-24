<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddContactBundle\Application\Query\FindContactSettings\FindContactSettingsQuery;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactSettings;
use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Resource\ContactSettingsResource;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Sylius\Component\Grid\Data\DataProviderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;

final readonly class ContactSettingsGridProvider implements DataProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {}

    /**
     * @return PagerfantaInterface<ContactSettingsResource>
     */
    public function getData(Grid $grid, Parameters $parameters): PagerfantaInterface
    {
        /** @var ContactSettings $settings */
        $settings = $this->queryBus->ask(new FindContactSettingsQuery());

        return new Pagerfanta(new FixedAdapter(1, [ContactSettingsResource::fromModel($settings)]));
    }
}
