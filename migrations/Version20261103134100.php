<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261103134100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE SealedProduct (productId INTEGER NOT NULL, name VARCHAR(255) NOT NULL, categoryName VARCHAR(255) DEFAULT NULL, expansionId INTEGER DEFAULT NULL, addedOn DATETIME DEFAULT NULL, data CLOB NOT NULL, PRIMARY KEY (productId))');
        $this->addSql('CREATE TABLE OwnedSealedProduct (productId INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, quantity INTEGER NOT NULL, paidPrice DOUBLE PRECISION DEFAULT NULL, addedOn DATETIME NOT NULL)');
        $this->addSql('CREATE TABLE SealedProductPrice (productId INTEGER NOT NULL, date DATE NOT NULL, currency VARCHAR(255) NOT NULL, low DOUBLE PRECISION DEFAULT NULL, avg DOUBLE PRECISION DEFAULT NULL, trend DOUBLE PRECISION DEFAULT NULL, avg1 DOUBLE PRECISION DEFAULT NULL, avg7 DOUBLE PRECISION DEFAULT NULL, avg30 DOUBLE PRECISION DEFAULT NULL, sourceUpdatedOn DATETIME DEFAULT NULL, data CLOB NOT NULL, PRIMARY KEY (productId, date))');
        $this->addSql('CREATE INDEX SealedProductPrice_date ON SealedProductPrice (date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE SealedProduct');
        $this->addSql('DROP TABLE OwnedSealedProduct');
        $this->addSql('DROP TABLE SealedProductPrice');
    }
}
