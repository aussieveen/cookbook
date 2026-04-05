<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\IngredientRepository;
use App\Service\UnitConverterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:backfill-base-units',
    description: 'Backfill base_quantity and base_unit for all existing ingredients',
)]
class BackfillBaseUnitsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private IngredientRepository $ingredientRepository,
        private UnitConverterService $unitConverter
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $batchSize = 100;
        $offset = 0;
        $total = 0;

        do {
            $ingredients = $this->ingredientRepository->findBy([], null, $batchSize, $offset);
            $fetched = count($ingredients);
            foreach ($ingredients as $ingredient) {
                $measurement = $ingredient->getRevisedMeasurement() ?? $ingredient->getMeasurement();
                if ($measurement !== null) {
                    $result = $this->unitConverter->convert($measurement);
                    $ingredient->setBaseQuantity($result['quantity']);
                    $ingredient->setBaseUnit($result['unit']);
                }
                $total++;
            }

            $this->entityManager->flush();
            $this->entityManager->clear();
            $offset += $batchSize;
        } while ($fetched === $batchSize);

        $io->success(sprintf('Backfilled %d ingredients.', $total));

        return Command::SUCCESS;
    }
}
