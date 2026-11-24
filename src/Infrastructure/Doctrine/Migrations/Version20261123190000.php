<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Doctrine\Migrations;

use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Creates the contact inbox and its settings.
 *
 * DBAL's built-in types only, never the bundle's own: a migration is a
 * snapshot, and must not change when a mapped type changes tomorrow. The
 * integration tests check that the result is the mapped schema on
 * PostgreSQL, MySQL and SQLite.
 */
final class Version20261123190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Contact: messages and settings.';
    }

    public function up(Schema $schema): void
    {
        $message = $schema->createTable('contact_message');
        $message->addColumn('id', Types::GUID);
        $message->addColumn('sender_name', Types::STRING, ['length' => 120]);
        $message->addColumn('sender_email', Types::STRING, ['length' => 180]);
        $message->addColumn('subject', Types::STRING, ['length' => 150, 'notnull' => false]);
        $message->addColumn('body', Types::TEXT);
        $message->addColumn('status', Types::STRING, ['length' => 20]);
        $message->addColumn('spam', Types::BOOLEAN);
        $message->addColumn('received_at', Types::DATETIME_IMMUTABLE);
        $message->addColumn('updated_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $message->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create());
        $message->addIndex(['received_at']);
        $message->addIndex(['status', 'spam']);

        $settings = $schema->createTable('contact_settings');
        $settings->addColumn('settings_key', Types::STRING, ['length' => 30]);
        $settings->addColumn('forward_to', Types::STRING, ['length' => 180, 'notnull' => false]);
        $settings->addColumn('updated_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $settings->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('settings_key')->create());
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('contact_settings');
        $schema->dropTable('contact_message');
    }
}
