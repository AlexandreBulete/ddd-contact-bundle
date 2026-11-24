<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Doctrine;

use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddContactBundle\Domain\Model\MessageStatus;
use AlexandreBulete\DddContactBundle\Domain\Repository\ContactMessageRepositoryInterface;
use AlexandreBulete\DddContactBundle\Domain\Service\DomainEventPublisherInterface;
use AlexandreBulete\DddDoctrineBridge\DoctrineRepository;
use AlexandreBulete\DddFoundation\Domain\ValueObject\IdentifierVO;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends DoctrineRepository<ContactMessage>
 */
final class DoctrineContactMessageRepository extends DoctrineRepository implements ContactMessageRepositoryInterface
{
    private const string ALIAS = 'message';

    public function __construct(
        EntityManagerInterface $em,
        private readonly DomainEventPublisherInterface $eventPublisher,
    ) {
        parent::__construct($em, ContactMessage::class, self::ALIAS);
    }

    public function save(ContactMessage $message): void
    {
        $this->em->wrapInTransaction(function () use ($message): void {
            $this->em->persist($message);
            $this->em->flush();
            $this->eventPublisher->publishAll($message->releaseEvents());
        });
    }

    public function remove(ContactMessage $message): void
    {
        $this->em->wrapInTransaction(function () use ($message): void {
            $this->em->remove($message);
            $this->em->flush();
            $this->eventPublisher->publishAll($message->releaseEvents());
        });
    }

    public function findById(IdentifierVO $id): ?ContactMessage
    {
        return $this->em->find(ContactMessage::class, $id->value());
    }

    public function countNew(): int
    {
        $count = $this->em->createQueryBuilder()
            ->select('COUNT(m.id)')
            ->from(ContactMessage::class, 'm')
            ->where('m.status = :status')
            ->andWhere('m.spam = :spam')
            ->setParameter('status', MessageStatus::New->value)
            ->setParameter('spam', false)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count;
    }
}
