<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create ingredient_name table and add nullable ingredient_name_id FK to ingredient';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ingredient_name (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_INGREDIENT_NAME (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ingredient ADD ingredient_name_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ingredient ADD CONSTRAINT FK_INGREDIENT_INGREDIENT_NAME FOREIGN KEY (ingredient_name_id) REFERENCES ingredient_name (id)');
        $this->addSql('CREATE INDEX IDX_INGREDIENT_INGREDIENT_NAME ON ingredient (ingredient_name_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ingredient DROP FOREIGN KEY FK_INGREDIENT_INGREDIENT_NAME');
        $this->addSql('DROP INDEX IDX_INGREDIENT_INGREDIENT_NAME ON ingredient');
        $this->addSql('ALTER TABLE ingredient DROP COLUMN ingredient_name_id');
        $this->addSql('DROP TABLE ingredient_name');
    }
}
