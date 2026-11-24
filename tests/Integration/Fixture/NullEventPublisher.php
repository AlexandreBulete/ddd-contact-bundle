<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Tests\Integration\Fixture;

use AlexandreBulete\DddContactBundle\Domain\Service\DomainEventPublisherInterface;

final class NullEventPublisher implements DomainEventPublisherInterface
{
    public function publishAll(iterable $events): void {}
}
