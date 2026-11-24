<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Domain\Repository;

use AlexandreBulete\DddContactBundle\Domain\Model\ContactMessage;
use AlexandreBulete\DddFoundation\Domain\Repository\RepositoryInterface;

/**
 * @extends RepositoryInterface<ContactMessage>
 */
interface ContactMessageRepositoryInterface extends RepositoryInterface
{
    public function save(ContactMessage $message): void;

    public function remove(ContactMessage $message): void;

    /**
     * Messages nobody opened yet, spam left out.
     */
    public function countNew(): int;
}
