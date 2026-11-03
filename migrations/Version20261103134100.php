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
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE SealedProduct');
    }
}
