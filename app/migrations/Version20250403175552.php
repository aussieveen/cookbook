<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250403175552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE component (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, recipe_id INT NOT NULL, INDEX IDX_49FEA15759D8A214 (recipe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) DEFAULT NULL, recipe_id INT NOT NULL, UNIQUE INDEX UNIQ_C53D045F59D8A214 (recipe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE ingredient (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, measurement VARCHAR(255) NOT NULL, revised_measurement VARCHAR(255) DEFAULT NULL, note VARCHAR(255) DEFAULT NULL, component_id INT NOT NULL, INDEX IDX_6BAF7870E2ABAFFF (component_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE mistake (id INT AUTO_INCREMENT NOT NULL, description LONGTEXT NOT NULL, fix LONGTEXT NOT NULL, recipe_id INT NOT NULL, INDEX IDX_F01D87B159D8A214 (recipe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE recipe (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, mastered TINYINT(1) DEFAULT NULL, UNIQUE INDEX UNIQ_DA88B1375E237E06 (name), UNIQUE INDEX UNIQ_DA88B137989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE step (id INT AUTO_INCREMENT NOT NULL, detail LONGTEXT NOT NULL, recipe_id INT NOT NULL, INDEX IDX_43B9FE3C59D8A214 (recipe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE component ADD CONSTRAINT FK_49FEA15759D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F59D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id)');
        $this->addSql('ALTER TABLE ingredient ADD CONSTRAINT FK_6BAF7870E2ABAFFF FOREIGN KEY (component_id) REFERENCES component (id)');
        $this->addSql('ALTER TABLE mistake ADD CONSTRAINT FK_F01D87B159D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id)');
        $this->addSql('ALTER TABLE step ADD CONSTRAINT FK_43B9FE3C59D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE component DROP FOREIGN KEY FK_49FEA15759D8A214');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F59D8A214');
        $this->addSql('ALTER TABLE ingredient DROP FOREIGN KEY FK_6BAF7870E2ABAFFF');
        $this->addSql('ALTER TABLE mistake DROP FOREIGN KEY FK_F01D87B159D8A214');
        $this->addSql('ALTER TABLE step DROP FOREIGN KEY FK_43B9FE3C59D8A214');
        $this->addSql('DROP TABLE component');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE ingredient');
        $this->addSql('DROP TABLE mistake');
        $this->addSql('DROP TABLE recipe');
        $this->addSql('DROP TABLE step');
    }
}
