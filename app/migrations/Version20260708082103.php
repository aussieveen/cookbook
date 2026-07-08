<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260708082103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Backfill needs_approval NULL -> 0 for all pre-existing recipes (approved by default)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE recipe SET needs_approval = 0 WHERE needs_approval IS NULL');
    }

    public function down(Schema $schema): void
    {
        // ponytail: not reversible — cannot distinguish "was NULL" from "was explicitly 0"
    }
}
