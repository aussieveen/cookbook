<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add base_quantity and base_unit columns to ingredient';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ingredient ADD base_quantity DOUBLE PRECISION DEFAULT NULL, ADD base_unit VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ingredient DROP COLUMN base_quantity, DROP COLUMN base_unit');
    }
}
