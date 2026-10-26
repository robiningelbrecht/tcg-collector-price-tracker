<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261026120543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX CardSet_name');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE INDEX CardSet_name ON CardSet (name)');
    }
}
