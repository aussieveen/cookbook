<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\ImportRecipeCardCommand;
use App\Entity\IngredientName;
use App\Repository\IngredientNameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ImportRecipeCardCommandTest extends TestCase
{
    private EntityManagerInterface&MockInterface $entityManager;
    private IngredientNameRepository&MockInterface $nameRepo;
    private CommandTester $commandTester;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->nameRepo = Mockery::mock(IngredientNameRepository::class);

        $command = new ImportRecipeCardCommand($this->entityManager, $this->nameRepo);
        $this->commandTester = new CommandTester($command);

        $this->tempDir = sys_get_temp_dir() . '/cookbook_test_' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tempDir . '/*') ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($this->tempDir)) {
            rmdir($this->tempDir);
        }

        Mockery::close();
    }

    public function testExecuteSucceedsWithSingleValidFile(): void
    {
        $file = $this->writeJson('recipe.json', $this->validRecipeData());

        $this->nameRepo
            ->shouldReceive('findOrCreate')
            ->andReturn($this->makeIngredientName('Flour'));

        $this->entityManager->shouldReceive('persist')->times(4);
        $this->entityManager->shouldReceive('flush')->once();

        $result = $this->commandTester->execute(['path' => $file]);

        $this->assertSame(Command::SUCCESS, $result);
        $this->assertStringContainsString('recipe.json', $this->commandTester->getDisplay());
    }

    public function testExecuteSucceedsWithDirectory(): void
    {
        $this->writeJson('a.json', $this->validRecipeData('Recipe A'));
        $this->writeJson('b.json', $this->validRecipeData('Recipe B'));

        $this->nameRepo
            ->shouldReceive('findOrCreate')
            ->andReturn($this->makeIngredientName('Flour'));

        $this->entityManager->shouldReceive('persist')->times(8);
        $this->entityManager->shouldReceive('flush')->twice();

        $result = $this->commandTester->execute(['path' => $this->tempDir]);

        $this->assertSame(Command::SUCCESS, $result);
    }

    public function testExecuteFailsWhenPathNotFound(): void
    {
        $result = $this->commandTester->execute(['path' => '/nonexistent/path/file.json']);

        $this->assertSame(Command::FAILURE, $result);
        $this->assertStringContainsString('Path not found', $this->commandTester->getDisplay());
    }

    public function testExecuteFailsWhenDirectoryHasNoJsonFiles(): void
    {
        $result = $this->commandTester->execute(['path' => $this->tempDir]);

        $this->assertSame(Command::FAILURE, $result);
        $this->assertStringContainsString('No JSON files found', $this->commandTester->getDisplay());
    }

    public function testExecuteReportsFailureForInvalidJson(): void
    {
        $file = $this->tempDir . '/bad.json';
        file_put_contents($file, 'not valid json');

        $result = $this->commandTester->execute(['path' => $file]);

        $this->assertSame(Command::FAILURE, $result);
    }

    public function testExecuteFailsWhenJsonMissingRequiredKeys(): void
    {
        $file = $this->writeJson('missing.json', ['name' => 'Incomplete Recipe']);

        $result = $this->commandTester->execute(['path' => $file]);

        $this->assertSame(Command::FAILURE, $result);
        $this->assertStringContainsString('missing.json', $this->commandTester->getDisplay());
    }

    public function testSachetMeasurementSetsRevisedMeasurement(): void
    {
        $data = [
            'name' => 'Sachet Recipe',
            'ingredients' => [
                ['name' => 'Spice mix', 'measurement' => '2 sachets'],
            ],
            'steps' => ['Mix and cook.'],
        ];
        $file = $this->writeJson('sachet.json', $data);

        $ingredientName = $this->makeIngredientName('Spice mix');
        $this->nameRepo
            ->shouldReceive('findOrCreate')
            ->with('Spice mix')
            ->andReturn($ingredientName);

        $persistedIngredients = [];
        $this->entityManager
            ->shouldReceive('persist')
            ->andReturnUsing(function ($entity) use (&$persistedIngredients) {
                if ($entity instanceof \App\Entity\Ingredient) {
                    $persistedIngredients[] = $entity;
                }
            });
        $this->entityManager->shouldReceive('flush');

        $this->commandTester->execute(['path' => $file]);

        $this->assertCount(1, $persistedIngredients);
        $this->assertSame('2 tbsp', $persistedIngredients[0]->getRevisedMeasurement());
    }

    private function validRecipeData(string $name = 'Test Recipe'): array
    {
        return [
            'name' => $name,
            'ingredients' => [
                ['name' => 'Flour', 'measurement' => '200g'],
            ],
            'steps' => ['Mix everything together.'],
        ];
    }

    private function writeJson(string $filename, array $data): string
    {
        $path = $this->tempDir . '/' . $filename;
        file_put_contents($path, json_encode($data));
        return $path;
    }

    private function makeIngredientName(string $name): IngredientName
    {
        $ingredientName = new IngredientName();
        $ingredientName->setName($name);
        return $ingredientName;
    }
}
