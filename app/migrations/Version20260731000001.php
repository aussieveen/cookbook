<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260731000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add category to ingredient_name';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE ingredient_name ADD category VARCHAR(30) DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ingredient_name DROP COLUMN category');
    }
}
