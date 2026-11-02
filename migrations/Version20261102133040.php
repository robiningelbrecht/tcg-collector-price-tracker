<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261102133040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE Binder (language VARCHAR(255) NOT NULL, cardSetId VARCHAR(255) NOT NULL, pageCount INTEGER NOT NULL, pocketsPerPage INTEGER NOT NULL, createdOn DATETIME NOT NULL, PRIMARY KEY (language, cardSetId))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE Binder');
    }
}
