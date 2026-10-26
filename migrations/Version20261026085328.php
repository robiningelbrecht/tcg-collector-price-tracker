<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261026085328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE CardSet (language VARCHAR(255) NOT NULL, cardSetId VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, serieId VARCHAR(255) DEFAULT NULL, serieName VARCHAR(255) DEFAULT NULL, releaseDate VARCHAR(255) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, symbol VARCHAR(255) DEFAULT NULL, cardCountOfficial INTEGER DEFAULT NULL, cardCountTotal INTEGER DEFAULT NULL, data CLOB NOT NULL, detailsImportedOn DATETIME DEFAULT NULL, PRIMARY KEY (language, cardSetId))');
        $this->addSql('CREATE INDEX CardSet_name ON CardSet (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE CardSet');
    }
}
