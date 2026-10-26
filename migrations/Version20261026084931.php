<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261026084931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE OwnedCard (tcgCollectorCardId INTEGER NOT NULL, variantTypeId INTEGER NOT NULL, languageId INTEGER NOT NULL, name VARCHAR(255) NOT NULL, setName VARCHAR(255) NOT NULL, cardNumber VARCHAR(255) NOT NULL, rarity VARCHAR(255) DEFAULT NULL, variantName VARCHAR(255) NOT NULL, region INTEGER NOT NULL, count INTEGER NOT NULL, tcgCollectorSlug VARCHAR(255) NOT NULL, importedOn DATETIME NOT NULL, PRIMARY KEY (tcgCollectorCardId, variantTypeId, languageId))');
        $this->addSql('CREATE INDEX OwnedCard_setName ON OwnedCard (setName)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE OwnedCard');
    }
}
