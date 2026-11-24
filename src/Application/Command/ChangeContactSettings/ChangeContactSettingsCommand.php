<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Application\Command\ChangeContactSettings;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddContactBundle\Domain\Model\ContactSettings;

/**
 * Saves the inbox settings.
 *
 * @implements CommandInterface<ContactSettings>
 */
final readonly class ChangeContactSettingsCommand implements CommandInterface
{
    public function __construct(
        public ?string $forwardTo,
    ) {}
}
