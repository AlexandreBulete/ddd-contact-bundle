<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Domain\Repository;

use AlexandreBulete\DddContactBundle\Domain\Model\ContactSettings;

interface ContactSettingsRepositoryInterface
{
    /**
     * The saved settings, or the defaults when none were saved yet.
     */
    public function get(): ContactSettings;

    public function save(ContactSettings $settings): void;
}
