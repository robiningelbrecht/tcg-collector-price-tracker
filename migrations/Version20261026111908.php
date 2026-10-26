<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261026111908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE OwnedCard ADD COLUMN variantType VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE OwnedCard ADD COLUMN variantSubtype VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE OwnedCard ADD COLUMN variantSize VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE OwnedCard ADD COLUMN variantStamps VARCHAR(255) DEFAULT NULL');
        $this->addSql("UPDATE OwnedCard SET (variantType, variantSubtype, variantSize, variantStamps) = (
            SELECT json_extract(variant.value, '$.type'), json_extract(variant.value, '$.subtype'), json_extract(variant.value, '$.size'),
                (SELECT group_concat(stamp.value, ',') FROM json_each(variant.value, '$.stamp') AS stamp)
            FROM json_each(OwnedCard.data, '$.variants_detailed') AS variant
            WHERE json_extract(variant.value, '$.variantId') = OwnedCard.priceVariantId
        )");
    }

    public function down(Schema $schema): void
    {

    }
}
