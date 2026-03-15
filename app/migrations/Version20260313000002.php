<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make ingredient_name_id NOT NULL and drop old ingredient.name column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ingredient MODIFY ingredient_name_id INT NOT NULL');
        $this->addSql('ALTER TABLE ingredient DROP COLUMN name');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ingredient ADD name VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE ingredient i JOIN ingredient_name n ON i.ingredient_name_id = n.id SET i.name = n.name');
        $this->addSql('ALTER TABLE ingredient MODIFY ingredient_name_id INT DEFAULT NULL');
    }
}
