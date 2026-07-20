<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260720000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'NULL course on recipes with removed Course values: breakfast, salad, soup, snack';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE recipe SET course = NULL WHERE course IN ('breakfast', 'salad', 'soup', 'snack')");
    }

    public function down(Schema $schema): void
    {
        // ponytail: not reversible — original course values are gone from the enum
    }
}
