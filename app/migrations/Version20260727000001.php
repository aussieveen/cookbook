<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260727000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add favourite flag to recipe';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE recipe ADD favourite TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE recipe DROP COLUMN favourite');
    }
}
