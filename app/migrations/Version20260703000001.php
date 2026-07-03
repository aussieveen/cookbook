<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260703000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add course column to recipe table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE recipe ADD course VARCHAR(20) DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE recipe DROP COLUMN course");
    }
}
