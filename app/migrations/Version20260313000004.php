<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313000004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create shopping_list_item table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE shopping_list_item (id INT AUTO_INCREMENT NOT NULL, recipe_id INT NOT NULL, UNIQUE INDEX UNIQ_SHOPPING_LIST_RECIPE (recipe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE shopping_list_item ADD CONSTRAINT FK_SHOPPING_LIST_RECIPE FOREIGN KEY (recipe_id) REFERENCES recipe (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE shopping_list_item DROP FOREIGN KEY FK_SHOPPING_LIST_RECIPE');
        $this->addSql('DROP TABLE shopping_list_item');
    }
}
