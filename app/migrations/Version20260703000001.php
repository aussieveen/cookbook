<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260703000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add course, meal_occasions, and pairs-with relationship to recipe';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE recipe ADD course VARCHAR(20) DEFAULT NULL");
        $this->addSql("ALTER TABLE recipe ADD meal_occasions JSON DEFAULT NULL");
        $this->addSql('CREATE TABLE recipe_pairing (recipe_id INT NOT NULL, paired_recipe_id INT NOT NULL, INDEX IDX_RECIPE_PAIRING_RECIPE (recipe_id), INDEX IDX_RECIPE_PAIRING_PAIRED (paired_recipe_id), PRIMARY KEY(recipe_id, paired_recipe_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE recipe_pairing ADD CONSTRAINT FK_RECIPE_PAIRING_RECIPE FOREIGN KEY (recipe_id) REFERENCES recipe (id)');
        $this->addSql('ALTER TABLE recipe_pairing ADD CONSTRAINT FK_RECIPE_PAIRING_PAIRED FOREIGN KEY (paired_recipe_id) REFERENCES recipe (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE recipe_pairing DROP FOREIGN KEY FK_RECIPE_PAIRING_RECIPE');
        $this->addSql('ALTER TABLE recipe_pairing DROP FOREIGN KEY FK_RECIPE_PAIRING_PAIRED');
        $this->addSql('DROP TABLE recipe_pairing');
        $this->addSql("ALTER TABLE recipe DROP COLUMN meal_occasions");
        $this->addSql("ALTER TABLE recipe DROP COLUMN course");
    }
}
