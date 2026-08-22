<?php

namespace Tests\Unit\Services;

use App\Exceptions\DictionaryGenerationException;
use App\Services\DictionaryGeneratorService;
use Illuminate\Support\Facades\File;
use Tests\Unit\UnitTestCase;

/**
 * Summary of DictionaryGeneratorServiceTest
 *
 * Serwis generuje pliki na realnym dysku (app_path()/base_path()), więc każdy
 * test sprząta po sobie w tearDown, żeby nie zostawić artefaktów w repo.
 */
final class DictionaryGeneratorServiceTest extends UnitTestCase
{
    /**
     * @var DictionaryGeneratorService
     */
    private DictionaryGeneratorService $service;

    /**
     * @var string
     */
    private string $routesPath;

    /**
     * @var string|null
     */
    private ?string $originalRoutesContent = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DictionaryGeneratorService;
        $this->routesPath = base_path('routes/api/dictionaries.php');
        $this->originalRoutesContent = File::exists($this->routesPath) ? File::get($this->routesPath) : null;
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        foreach ($this->generatedDirectories() as $directory) {
            if (File::isDirectory($directory)) {
                File::deleteDirectory($directory);
            }
        }

        if ($this->originalRoutesContent !== null) {
            File::put($this->routesPath, $this->originalRoutesContent);
        }

        parent::tearDown();
    }

    /**
     * @return string[]
     */
    private function generatedDirectories(): array
    {
        return [
            app_path('Models/Dictionaries'),
            app_path('Search/Dictionaries'),
            app_path('Repositories/Dictionaries'),
            app_path('Services/Dictionaries'),
            app_path('Http/Controllers/Dictionaries'),
        ];
    }

    /**
     * @return void
     */
    public function testGenerateWithEmptyNameThrowsException(): void
    {
        $this->expectException(DictionaryGenerationException::class);

        $this->service->generate('   ');
    }

    /**
     * @return void
     */
    public function testGenerateCreatesAllFilesWithReplacedPlaceholders(): void
    {
        $messages = $this->service->generate('GeneratorProbe');

        $modelPath = app_path('Models/Dictionaries/GeneratorProbeDictionary.php');
        $searchPath = app_path('Search/Dictionaries/GeneratorProbeDictionarySearch.php');
        $repositoryPath = app_path('Repositories/Dictionaries/GeneratorProbeDictionaryRepository.php');
        $servicePath = app_path('Services/Dictionaries/GeneratorProbeDictionaryService.php');
        $controllerPath = app_path('Http/Controllers/Dictionaries/GeneratorProbeDictionaryController.php');

        $this->assertFileExists($modelPath);
        $this->assertFileExists($searchPath);
        $this->assertFileExists($repositoryPath);
        $this->assertFileExists($servicePath);
        $this->assertFileExists($controllerPath);

        $this->assertStringContainsString('namespace App\Models\Dictionaries;', File::get($modelPath));
        $this->assertStringContainsString('class GeneratorProbeDictionary', File::get($modelPath));
        $this->assertStringNotContainsString('{{ class }}', File::get($modelPath));

        $this->assertStringContainsString('class GeneratorProbeDictionaryController', File::get($controllerPath));
        $this->assertStringNotContainsString('{{ routeSlug }}', File::get($controllerPath));

        $this->assertNotEmpty($messages);
        $this->assertStringContainsString('GeneratorProbeDictionary', end($messages));
    }

    /**
     * @return void
     */
    public function testGenerateAddsRouteEntryToDictionaryRoutesFile(): void
    {
        $this->service->generate('GeneratorProbe');

        $routes = File::get($this->routesPath);

        $this->assertStringContainsString('GeneratorProbeDictionaryController', $routes);
        $this->assertStringContainsString("Route::apiResource('generator-probes'", $routes);
    }

    /**
     * @return void
     */
    public function testGenerateTwiceForSameNameThrowsAlreadyExistsException(): void
    {
        $this->service->generate('GeneratorProbe');

        $this->expectException(DictionaryGenerationException::class);

        $this->service->generate('GeneratorProbe');
    }
}
