<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261026112143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE CardSet (language VARCHAR(255) NOT NULL, cardSetId VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, serieId VARCHAR(255) DEFAULT NULL, serieName VARCHAR(255) DEFAULT NULL, releaseDate VARCHAR(255) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, symbol VARCHAR(255) DEFAULT NULL, cardCountOfficial INTEGER DEFAULT NULL, cardCountTotal INTEGER DEFAULT NULL, data CLOB NOT NULL, detailsImportedOn DATETIME DEFAULT NULL, PRIMARY KEY (language, cardSetId))');
        $this->addSql('CREATE INDEX CardSet_name ON CardSet (name)');
        $this->addSql('CREATE TABLE OwnedCard (cardId VARCHAR(255) NOT NULL, priceVariantId VARCHAR(255) NOT NULL, variantType VARCHAR(255) DEFAULT NULL, variantSubtype VARCHAR(255) DEFAULT NULL, variantSize VARCHAR(255) DEFAULT NULL, variantStamps VARCHAR(255) DEFAULT NULL, count INTEGER NOT NULL, language VARCHAR(255) NOT NULL, cardSetId VARCHAR(255) NOT NULL, localId VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, category VARCHAR(255) DEFAULT NULL, rarity VARCHAR(255) DEFAULT NULL, illustrator VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, hp INTEGER DEFAULT NULL, data CLOB NOT NULL, importedOn DATETIME NOT NULL, PRIMARY KEY (cardId, priceVariantId))');
        $this->addSql('CREATE INDEX OwnedCard_card ON OwnedCard (language, cardId)');
        $this->addSql('CREATE INDEX OwnedCard_cardSet ON OwnedCard (language, cardSetId)');
        $this->addSql('CREATE TABLE CardPrice (language VARCHAR(255) NOT NULL, cardId VARCHAR(255) NOT NULL, variantId VARCHAR(255) NOT NULL, source VARCHAR(255) NOT NULL, date DATE NOT NULL, variantType VARCHAR(255) DEFAULT NULL, variantSubtype VARCHAR(255) DEFAULT NULL, variantSize VARCHAR(255) DEFAULT NULL, variantStamps VARCHAR(255) DEFAULT NULL, currency VARCHAR(255) NOT NULL, low DOUBLE PRECISION DEFAULT NULL, mid DOUBLE PRECISION DEFAULT NULL, high DOUBLE PRECISION DEFAULT NULL, market DOUBLE PRECISION DEFAULT NULL, avg DOUBLE PRECISION DEFAULT NULL, trend DOUBLE PRECISION DEFAULT NULL, avg1 DOUBLE PRECISION DEFAULT NULL, avg7 DOUBLE PRECISION DEFAULT NULL, avg30 DOUBLE PRECISION DEFAULT NULL, sourceUpdatedOn DATETIME DEFAULT NULL, data CLOB NOT NULL, PRIMARY KEY (language, cardId, variantId, source, date))');
        $this->addSql('CREATE INDEX CardPrice_date ON CardPrice (date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE CardPrice');
        $this->addSql('DROP TABLE OwnedCard');
        $this->addSql('DROP TABLE CardSet');
    }
}
