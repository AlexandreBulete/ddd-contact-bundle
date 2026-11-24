<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddContactBundle\Domain\ValueObject\ContactMessageId;
use AlexandreBulete\DddDoctrineBridge\Type\GuidType;

final class ContactMessageIdType extends GuidType
{
    public const string NAME = 'contact_message_id';

    protected string $name = self::NAME;

    protected string $voClass = ContactMessageId::class;
}
