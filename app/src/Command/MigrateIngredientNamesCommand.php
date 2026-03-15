<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-ingredient-names',
    description: 'Populate ingredient_name table from existing ingredient names and link them',
)]
class MigrateIngredientNamesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $conn = $this->entityManager->getConnection();

        $names = $conn->fetchAllAssociative('SELECT DISTINCT name FROM ingredient WHERE name IS NOT NULL');

        $io->progressStart(count($names));

        foreach ($names as $row) {
            $name = $row['name'];

            $existing = $conn->fetchAssociative(
                'SELECT id FROM ingredient_name WHERE LOWER(name) = LOWER(?)',
                [$name]
            );

            if ($existing !== false) {
                $id = $existing['id'];
            } else {
                $conn->executeStatement('INSERT INTO ingredient_name (name) VALUES (?)', [$name]);
                $id = $conn->lastInsertId();
            }

            $conn->executeStatement(
                'UPDATE ingredient SET ingredient_name_id = ? WHERE LOWER(name) = LOWER(?)',
                [$id, $name]
            );

            $io->progressAdvance();
        }

        $io->progressFinish();

        $nullCount = $conn->fetchOne('SELECT COUNT(*) FROM ingredient WHERE ingredient_name_id IS NULL');

        if ($nullCount > 0) {
            $io->error(sprintf('%d ingredients still have NULL ingredient_name_id!', $nullCount));
            return Command::FAILURE;
        }

        $io->success(sprintf('Migrated %d distinct ingredient names. No NULLs remaining.', count($names)));

        return Command::SUCCESS;
    }
}
