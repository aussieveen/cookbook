<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260707181403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ingredient RENAME INDEX idx_ingredient_ingredient_name TO IDX_6BAF787070209710');
        $this->addSql('ALTER TABLE ingredient_name RENAME INDEX uniq_ingredient_name TO UNIQ_72CE905E237E06');
        $this->addSql('ALTER TABLE recipe ADD needs_approval TINYINT DEFAULT NULL');
        $this->addSql('ALTER TABLE recipe_pairing RENAME INDEX idx_recipe_pairing_recipe TO IDX_C4F2E2BC59D8A214');
        $this->addSql('ALTER TABLE recipe_pairing RENAME INDEX idx_recipe_pairing_paired TO IDX_C4F2E2BC97094A3C');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ingredient RENAME INDEX idx_6baf787070209710 TO IDX_INGREDIENT_INGREDIENT_NAME');
        $this->addSql('ALTER TABLE ingredient_name RENAME INDEX uniq_72ce905e237e06 TO UNIQ_INGREDIENT_NAME');
        $this->addSql('ALTER TABLE recipe DROP needs_approval');
        $this->addSql('ALTER TABLE recipe_pairing RENAME INDEX idx_c4f2e2bc97094a3c TO IDX_RECIPE_PAIRING_PAIRED');
        $this->addSql('ALTER TABLE recipe_pairing RENAME INDEX idx_c4f2e2bc59d8a214 TO IDX_RECIPE_PAIRING_RECIPE');
    }
}
