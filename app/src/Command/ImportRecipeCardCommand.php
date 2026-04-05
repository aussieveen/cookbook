<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Component;
use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\Step;
use App\Repository\IngredientNameRepository;
use RuntimeException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-recipe-card',
    description: 'Import a HelloFresh recipe card from a JSON file',
)]
class ImportRecipeCardCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private IngredientNameRepository $ingredientNameRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::REQUIRED, 'Path to a recipe JSON file or a folder of JSON files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('path');

        if (!is_dir($path) && !file_exists($path)) {
            $io->error("Path not found: $path");
            return Command::FAILURE;
        }

        $files = [$path];
        if (is_dir($path)) {
            $files = glob(rtrim($path, '/') . '/*.json') ?: [];
            if (empty($files)) {
                $io->error("No JSON files found in: $path");
                return Command::FAILURE;
            }
            $io->text(sprintf('Found %d JSON files.', count($files)));
        }

        $imported = 0;
        $failed = 0;

        foreach ($files as $file) {
            try {
                $this->importFile($file);
                $io->text(sprintf('  <info>✓</info> %s', basename($file)));
                $imported++;
            } catch (\Throwable $e) {
                $io->text(sprintf('  <error>✗</error> %s — %s', basename($file), $e->getMessage()));
                $failed++;
            }
        }

        if ($failed > 0) {
            $io->warning(sprintf('Imported %d, failed %d.', $imported, $failed));
            return Command::FAILURE;
        }

        $io->success(sprintf('Imported %d recipe(s) successfully.', $imported));
        return Command::SUCCESS;
    }

    private function importFile(string $file): void
    {
        $data = json_decode(file_get_contents($file), true);

        if (!is_array($data) || !isset($data['name'], $data['ingredients'], $data['steps'])) {
            throw new RuntimeException('Invalid JSON structure. Expected keys: name, ingredients, steps.');
        }

        $recipe = new Recipe();
        $recipe->setName($data['name']);

        $component = new Component();
        $component->setName(null);
        $component->setRecipe($recipe);
        $recipe->addComponent($component);

        foreach ($data['ingredients'] as $item) {
            $ingredientName = $this->ingredientNameRepository->findOrCreate($item['name']);
            $measurement = $item['measurement'];
            $revisedMeasurement = $this->sachetToTbsp($measurement);

            $ingredient = new Ingredient();
            $ingredient->setIngredientName($ingredientName);
            $ingredient->setMeasurement($measurement);
            if ($revisedMeasurement !== null) {
                $ingredient->setRevisedMeasurement($revisedMeasurement);
            }
            $component->addIngredient($ingredient);
            $this->entityManager->persist($ingredient);
        }

        foreach ($data['steps'] as $detail) {
            $step = new Step();
            $step->setDetail($detail);
            $recipe->addStep($step);
            $this->entityManager->persist($step);
        }

        $this->entityManager->persist($component);
        $this->entityManager->persist($recipe);
        $this->entityManager->flush();
    }

    private function sachetToTbsp(string $measurement): ?string
    {
        if (!preg_match('/(\d+(?:\.\d+)?)\s*sachet/i', $measurement, $matches)) {
            return null;
        }

        $tbsp = (float) $matches[1];

        return $tbsp === floor($tbsp)
            ? ((int) $tbsp) . ' tbsp'
            : $tbsp . ' tbsp';
    }
}
