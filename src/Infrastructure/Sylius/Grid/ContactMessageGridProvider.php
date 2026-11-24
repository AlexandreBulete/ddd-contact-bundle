<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddContactBundle\Application\Query\FindContactMessages\FindContactMessagesQuery;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddContactBundle\Domain\Repository\ContactMessageRepositoryInterface;
use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Resource\ContactMessageResource;
use AlexandreBulete\DddContactBundle\Infrastructure\Sylius\Resource\ContactMessageResourceFactory;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use AlexandreBulete\DddSyliusBundle\Grid\GridPageResolver;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Sylius\Component\Grid\Data\DataProviderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;

final readonly class ContactMessageGridProvider implements DataProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private ContactMessageResourceFactory $resources,
    ) {}

    /**
     * @return PagerfantaInterface<ContactMessageResource>
     */
    public function getData(Grid $grid, Parameters $parameters): PagerfantaInterface
    {
        /** @var array<string, mixed> $criteria */
        $criteria = $parameters->get('criteria', []);
        /** @var array<string, string> $sorting */
        $sorting = $parameters->get('sorting', $grid->getSorting());

        /** @var ContactMessageRepositoryInterface $models */
        $models = $this->queryBus->ask(new FindContactMessagesQuery(
            page: GridPageResolver::getCurrentPage($grid, $parameters),
            itemsPerPage: GridPageResolver::getItemsPerPage($grid, $parameters),
            criteria: $criteria,
            withSorting: $sorting,
        ));

        $data = [];
        foreach ($models as $model) {
            /** @var ContactMessage $model */
            $data[] = $this->resources->fromModel($model);
        }

        return new Pagerfanta(new FixedAdapter($models->count(), $data));
    }
}
