<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260809000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make ingredient measurement nullable; add recipe type column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE ingredient MODIFY measurement VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE recipe ADD type VARCHAR(10) DEFAULT NULL");
        $this->addSql("UPDATE recipe SET type = 'recipe' WHERE type IS NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE ingredient SET measurement = '' WHERE measurement IS NULL");
        $this->addSql("ALTER TABLE ingredient MODIFY measurement VARCHAR(255) NOT NULL");
        $this->addSql("ALTER TABLE recipe DROP COLUMN type");
    }
}
