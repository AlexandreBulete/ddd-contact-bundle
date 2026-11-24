<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Doctrine;

use AlexandreBulete\DddContactBundle\Domain\Model\ContactSettings;
use AlexandreBulete\DddContactBundle\Domain\Repository\ContactSettingsRepositoryInterface;
use AlexandreBulete\DddContactBundle\Domain\Service\DomainEventPublisherInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineContactSettingsRepository implements ContactSettingsRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private DomainEventPublisherInterface $eventPublisher,
    ) {}

    public function get(): ContactSettings
    {
        return $this->em->find(ContactSettings::class, ContactSettings::KEY) ?? ContactSettings::defaults();
    }

    public function save(ContactSettings $settings): void
    {
        $this->em->wrapInTransaction(function () use ($settings): void {
            $this->em->persist($settings);
            $this->em->flush();
            $this->eventPublisher->publishAll($settings->releaseEvents());
        });
    }
}
