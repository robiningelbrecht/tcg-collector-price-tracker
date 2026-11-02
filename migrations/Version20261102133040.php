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
        $this->addSql('CREATE TABLE BinderPage (language VARCHAR(255) NOT NULL, cardSetId VARCHAR(255) NOT NULL, pageNumber INTEGER NOT NULL, fileName VARCHAR(255) NOT NULL, takenOn DATETIME NOT NULL, uploadedOn DATETIME NOT NULL, PRIMARY KEY (language, cardSetId, pageNumber))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE Binder');
        $this->addSql('DROP TABLE BinderPage');
    }
}
