<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261026084931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE OwnedCard (tcgCollectorCardId INTEGER NOT NULL, variantTypeId INTEGER NOT NULL, languageId INTEGER NOT NULL, variantName VARCHAR(255) NOT NULL, count INTEGER NOT NULL, language VARCHAR(255) NOT NULL, cardId VARCHAR(255) NOT NULL, cardSetId VARCHAR(255) NOT NULL, localId VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, category VARCHAR(255) DEFAULT NULL, rarity VARCHAR(255) DEFAULT NULL, illustrator VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, hp INTEGER DEFAULT NULL, data CLOB NOT NULL, importedOn DATETIME NOT NULL, PRIMARY KEY (tcgCollectorCardId, variantTypeId, languageId))');
        $this->addSql('CREATE INDEX OwnedCard_card ON OwnedCard (language, cardId)');
        $this->addSql('CREATE INDEX OwnedCard_cardSet ON OwnedCard (language, cardSetId)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE OwnedCard');
    }
}
