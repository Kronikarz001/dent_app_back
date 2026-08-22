<?php

namespace App\Services;

use App\Exceptions\DictionaryGenerationException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Summary of DictionaryGeneratorService
 */
class DictionaryGeneratorService
{
    /**
     * @param string $name
     * @return array<int, string>
     */
    public function generate(string $name): array
    {
        $name = trim($name);

        if ($name === '') {
            throw new DictionaryGenerationException('Musisz podać nazwę słownika przy pomocy parametru --name.');
        }

        $dictionaryClass = $this->formatDictionaryClass($name);
        $baseName = Str::replaceLast('Dictionary', '', $dictionaryClass) ?: $dictionaryClass;

        $searchClass = $dictionaryClass.'Search';
        $repositoryClass = $dictionaryClass.'Repository';
        $serviceClass = $dictionaryClass.'Service';
        $controllerClass = $dictionaryClass.'Controller';

        $routeSlug = Str::kebab(Str::pluralStudly($baseName)) ?: Str::kebab($dictionaryClass);
        $routeName = Str::snake(Str::pluralStudly($baseName)) ?: Str::snake($dictionaryClass);

        $messages = [];

        foreach ($this->fileGenerators($dictionaryClass, $searchClass, $repositoryClass, $serviceClass, $controllerClass, $routeSlug) as $generator) {
            $messages[] = $this->generateFileFromStub(
                $generator['path'],
                $generator['stub'],
                $generator['replacements'],
                $generator['label']
            );
        }

        $messages[] = $this->ensureDictionaryRoutesFileExists();
        $messages[] = $this->ensureDictionaryRoutesAreLoaded();
        $messages[] = $this->appendDictionaryRoute($routeSlug, $routeName, $controllerClass);
        $messages[] = "Słownik {$dictionaryClass} został w pełni utworzony.";

        return array_values(array_filter($messages));
    }

    /**
     * @param string $dictionaryClass
     * @param string $searchClass
     * @param string $repositoryClass
     * @param string $serviceClass
     * @param string $controllerClass
     * @param string $routeSlug
     * @return array<int, array{path: string, stub: string, label: string, replacements: array<string, string>}>
     */
    private function fileGenerators(
        string $dictionaryClass,
        string $searchClass,
        string $repositoryClass,
        string $serviceClass,
        string $controllerClass,
        string $routeSlug,
    ): array {
        $modelFQN = 'App\\Models\\Dictionaries\\'.$dictionaryClass;

        return [
            [
                'path' => app_path("Models/Dictionaries/{$dictionaryClass}.php"),
                'stub' => base_path('stubs/dictionary.stub'),
                'label' => 'Model',
                'replacements' => [
                    '{{ namespace }}' => 'App\\Models\\Dictionaries',
                    '{{ class }}' => $dictionaryClass,
                ],
            ],
            [
                'path' => app_path("Search/Dictionaries/{$searchClass}.php"),
                'stub' => base_path('stubs/dictionary-search.stub'),
                'label' => 'Search',
                'replacements' => [
                    '{{ namespace }}' => 'App\\Search\\Dictionaries',
                    '{{ class }}' => $searchClass,
                    '{{ modelFQN }}' => $modelFQN,
                    '{{ model }}' => $dictionaryClass,
                ],
            ],
            [
                'path' => app_path("Repositories/Dictionaries/{$repositoryClass}.php"),
                'stub' => base_path('stubs/dictionary-repository.stub'),
                'label' => 'Repozytorium',
                'replacements' => [
                    '{{ namespace }}' => 'App\\Repositories\\Dictionaries',
                    '{{ class }}' => $repositoryClass,
                    '{{ search }}' => $searchClass,
                    '{{ modelFQN }}' => $modelFQN,
                    '{{ model }}' => $dictionaryClass,
                ],
            ],
            [
                'path' => app_path("Services/Dictionaries/{$serviceClass}.php"),
                'stub' => base_path('stubs/dictionary-service.stub'),
                'label' => 'Serwis',
                'replacements' => [
                    '{{ namespace }}' => 'App\\Services\\Dictionaries',
                    '{{ class }}' => $serviceClass,
                    '{{ model }}' => $dictionaryClass,
                    '{{ repository }}' => $repositoryClass,
                ],
            ],
            [
                'path' => app_path("Http/Controllers/Dictionaries/{$controllerClass}.php"),
                'stub' => base_path('stubs/dictionary-controller.stub'),
                'label' => 'Kontroler',
                'replacements' => [
                    '{{ namespace }}' => 'App\\Http\\Controllers\\Dictionaries',
                    '{{ class }}' => $controllerClass,
                    '{{ model }}' => $dictionaryClass,
                    '{{ service }}' => $serviceClass,
                    '{{ routeSlug }}' => $routeSlug,
                ],
            ],
        ];
    }

    /**
     * @param string $name
     * @return string
     */
    private function formatDictionaryClass(string $name): string
    {
        $className = Str::studly($name);

        if (! Str::endsWith($className, 'Dictionary')) {
            $className .= 'Dictionary';
        }

        return $className;
    }

    /**
     * @param string $path
     * @param string $stubPath
     * @param array<string, string> $replacements
     * @param string $label
     * @return string
     */
    private function generateFileFromStub(string $path, string $stubPath, array $replacements, string $label): string
    {
        if (File::exists($path)) {
            throw new DictionaryGenerationException("{$label} już istnieje: {$path}");
        }

        if (! File::exists($stubPath)) {
            throw new DictionaryGenerationException("Nie znaleziono pliku stub: {$stubPath}");
        }

        $this->ensureDirectory(dirname($path));
        $content = str_replace(array_keys($replacements), array_values($replacements), File::get($stubPath));
        File::put($path, $content);

        return "{$label} - plik został utworzony: {$path}";
    }

    /**
     * @param string $directory
     * @return void
     */
    private function ensureDirectory(string $directory): void
    {
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    /**
     * @return string|null
     */
    private function ensureDictionaryRoutesFileExists(): ?string
    {
        $routesPath = $this->dictionaryRoutesPath();

        if (File::exists($routesPath)) {
            return null;
        }

        $stubPath = base_path('stubs/dictionary-routes.stub');

        if (! File::exists($stubPath)) {
            throw new DictionaryGenerationException("Nie znaleziono pliku stub dla tras: {$stubPath}");
        }

        $this->ensureDirectory(dirname($routesPath));
        File::put($routesPath, File::get($stubPath));

        return 'Utworzono plik routes/api/dictionaries.php.';
    }

    /**
     * @return string|null
     */
    private function ensureDictionaryRoutesAreLoaded(): ?string
    {
        $apiRoutesPath = base_path('routes/api.php');
        $includeLine = "require __DIR__.'/api/dictionaries.php';";
        $content = File::get($apiRoutesPath);

        if (Str::contains($content, $includeLine)) {
            return null;
        }

        File::append($apiRoutesPath, PHP_EOL.$includeLine.PHP_EOL);

        return 'Dodano załączanie routes/api/dictionaries.php w routes/api.php.';
    }

    /**
     * @param string $routeSlug
     * @param string $routeName
     * @param string $controllerClass
     * @return string
     */
    private function appendDictionaryRoute(string $routeSlug, string $routeName, string $controllerClass): string
    {
        $routesPath = $this->dictionaryRoutesPath();

        if (! File::exists($routesPath)) {
            throw new DictionaryGenerationException('Brak pliku routes/api/dictionaries.php. Przerwano dodawanie trasy.');
        }

        $routes = File::get($routesPath);

        if (Str::contains($routes, $controllerClass.'::class')) {
            return 'Trasa dla tego słownika już istnieje.';
        }

        $entry = "        Route::apiResource('{$routeSlug}', \\App\\Http\\Controllers\\Dictionaries\\{$controllerClass}::class)".PHP_EOL
            ."            ->names('{$routeName}')".PHP_EOL
            ."            ->parameter('{$routeSlug}', 'dictionary');".PHP_EOL;

        $placeholder = '// Dictionary API routes';

        if (Str::contains($routes, $placeholder)) {
            File::put($routesPath, str_replace($placeholder, $placeholder.PHP_EOL.$entry, $routes));

            return 'Dodano trasę w routes/api/dictionaries.php.';
        }

        $updatedRoutes = preg_replace('/(\s*\}\);\s*)$/', PHP_EOL.$entry.'$1', $routes, 1, $replacementsCount);
        File::put($routesPath, $replacementsCount > 0 ? $updatedRoutes : $routes.PHP_EOL.$entry);

        return 'Dodano trasę w routes/api/dictionaries.php.';
    }

    /**
     * @return string
     */
    private function dictionaryRoutesPath(): string
    {
        return base_path('routes/api/dictionaries.php');
    }
}
