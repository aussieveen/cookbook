<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\IngredientSuggestionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'app:ingredient-suggestions:run',
    description: 'Manually trigger the AI ingredient merge and category suggestion generation',
)]
class GenerateIngredientSuggestionsCommand extends Command
{
    public function __construct(private readonly IngredientSuggestionService $service)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info('Generating ingredient suggestions via Claude...');

        try {
            $this->service->run();
            $io->success('Done. Suggestions updated.');
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
