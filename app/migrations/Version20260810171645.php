<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260810171645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ingredient_category_suggestion (id INT AUTO_INCREMENT NOT NULL, suggested_category VARCHAR(255) NOT NULL, suggested_at DATETIME NOT NULL, dismissed_at DATETIME DEFAULT NULL, ingredient_name_id INT NOT NULL, INDEX IDX_FA72D4D370209710 (ingredient_name_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE ingredient_merge_suggestion (id INT AUTO_INCREMENT NOT NULL, suggested_at DATETIME NOT NULL, dismissed_at DATETIME DEFAULT NULL, name_a_id INT NOT NULL, name_b_id INT NOT NULL, INDEX IDX_801A53777C68892F (name_a_id), INDEX IDX_801A53776EDD26C1 (name_b_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ingredient_category_suggestion ADD CONSTRAINT FK_FA72D4D370209710 FOREIGN KEY (ingredient_name_id) REFERENCES ingredient_name (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ingredient_merge_suggestion ADD CONSTRAINT FK_801A53777C68892F FOREIGN KEY (name_a_id) REFERENCES ingredient_name (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ingredient_merge_suggestion ADD CONSTRAINT FK_801A53776EDD26C1 FOREIGN KEY (name_b_id) REFERENCES ingredient_name (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ingredient_name CHANGE category category VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ingredient_category_suggestion DROP FOREIGN KEY FK_FA72D4D370209710');
        $this->addSql('ALTER TABLE ingredient_merge_suggestion DROP FOREIGN KEY FK_801A53777C68892F');
        $this->addSql('ALTER TABLE ingredient_merge_suggestion DROP FOREIGN KEY FK_801A53776EDD26C1');
        $this->addSql('DROP TABLE ingredient_category_suggestion');
        $this->addSql('DROP TABLE ingredient_merge_suggestion');
        $this->addSql('ALTER TABLE ingredient_name CHANGE category category VARCHAR(30) DEFAULT NULL');
    }
}
