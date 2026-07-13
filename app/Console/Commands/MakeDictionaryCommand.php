<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Summary of MakeDictionaryCommand
 */
class MakeDictionaryCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'make:dictionary {--name= : Nazwa klasy słownika}';

    /**
     * @var string
     */
    protected $description = 'Tworzy moduł słownika (model, repozytorium, serwis, kontroler i trasy)';

    /**
     * @return int
     */
    public function handle(): int
    {
        $name = trim((string) $this->option('name'));

        if ($name === '') {
            $this->error('Musisz podać nazwę słownika przy pomocy parametru --name.');

            return 1;
        }

        $dictionaryClass = $this->formatDictionaryClass($name);
        $baseName = Str::replaceLast('Dictionary', '', $dictionaryClass) ?: $dictionaryClass;

        $searchClass = $dictionaryClass.'Search';
        $repositoryClass = $dictionaryClass.'Repository';
        $serviceClass = $dictionaryClass.'Service';
        $controllerClass = $dictionaryClass.'Controller';

        $modelNamespace = 'App\\Models\\Dictionaries';
        $searchNamespace = 'App\\Search\\Dictionaries';
        $repositoryNamespace = 'App\\Repositories\\Dictionaries';
        $serviceNamespace = 'App\\Services\\Dictionaries';
        $controllerNamespace = 'App\\Http\\Controllers\\Dictionaries';

        $routeSlug = Str::kebab(Str::pluralStudly($baseName));
        $routeName = Str::snake(Str::pluralStudly($baseName));

        if ($routeSlug === '') {
            $routeSlug = Str::kebab($dictionaryClass);
        }

        if ($routeName === '') {
            $routeName = Str::snake($dictionaryClass);
        }

        $generators = [
            [
                'path' => app_path("Models/Dictionaries/{$dictionaryClass}.php"),
                'stub' => base_path('stubs/dictionary.stub'),
                'label' => 'Model',
                'replacements' => [
                    '{{ namespace }}' => $modelNamespace,
                    '{{ class }}' => $dictionaryClass,
                ],
            ],
            [
                'path' => app_path("Search/Dictionaries/{$searchClass}.php"),
                'stub' => base_path('stubs/dictionary-search.stub'),
                'label' => 'Search',
                'replacements' => [
                    '{{ namespace }}' => $searchNamespace,
                    '{{ class }}' => $searchClass,
                    '{{ modelFQN }}' => $modelNamespace.'\\'.$dictionaryClass,
                    '{{ model }}' => $dictionaryClass,
                ],
            ],
            [
                'path' => app_path("Repositories/Dictionaries/{$repositoryClass}.php"),
                'stub' => base_path('stubs/dictionary-repository.stub'),
                'label' => 'Repozytorium',
                'replacements' => [
                    '{{ namespace }}' => $repositoryNamespace,
                    '{{ class }}' => $repositoryClass,
                    '{{ search }}' => $searchClass,
                    '{{ modelFQN }}' => $modelNamespace.'\\'.$dictionaryClass,
                    '{{ model }}' => $dictionaryClass,
                ],
            ],
            [
                'path' => app_path("Services/Dictionaries/{$serviceClass}.php"),
                'stub' => base_path('stubs/dictionary-service.stub'),
                'label' => 'Serwis',
                'replacements' => [
                    '{{ namespace }}' => $serviceNamespace,
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
                    '{{ namespace }}' => $controllerNamespace,
                    '{{ class }}' => $controllerClass,
                    '{{ model }}' => $dictionaryClass,
                    '{{ service }}' => $serviceClass,
                    '{{ routeSlug }}' => $routeSlug,
                ],
            ],
        ];

        foreach ($generators as $generator) {
            if (! $this->generateFileFromStub(
                $generator['path'],
                $generator['stub'],
                $generator['replacements'],
                $generator['label']
            )) {
                return 1;
            }
        }

        $this->ensureDictionaryRoutesFileExists();
        $this->ensureDictionaryRoutesAreLoaded();

        $this->appendDictionaryRoute($routeSlug, $routeName, $controllerClass);

        $this->info("Słownik {$dictionaryClass} został w pełni utworzony.");

        return 0;
    }

    private function formatDictionaryClass(string $name): string
    {
        $className = Str::studly($name);

        if (! Str::endsWith($className, 'Dictionary')) {
            $className .= 'Dictionary';
        }

        return $className;
    }

    private function generateFileFromStub(string $path, string $stubPath, array $replacements, string $label): bool
    {
        if (File::exists($path)) {
            $this->error("{$label} już istnieje: {$path}");

            return false;
        }

        if (! File::exists($stubPath)) {
            $this->error("Nie znaleziono pliku stub: {$stubPath}");

            return false;
        }

        $this->ensureDirectory(dirname($path));
        $content = str_replace(array_keys($replacements), array_values($replacements), File::get($stubPath));
        File::put($path, $content);
        $this->info("{$label} - plik został utworzony: {$path}");

        return true;
    }

    private function ensureDirectory(string $directory): void
    {
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    private function ensureDictionaryRoutesFileExists(): void
    {
        $routesPath = $this->dictionaryRoutesPath();

        if (! File::exists($routesPath)) {
            $stubPath = base_path('stubs/dictionary-routes.stub');

            if (! File::exists($stubPath)) {
                $this->error("Nie znaleziono pliku stub dla tras: {$stubPath}");

                return;
            }

            $directory = dirname($routesPath);
            if (! File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            File::put($routesPath, File::get($stubPath));
            $this->info('Utworzono plik routes/api/dictionaries.php.');
        }
    }

    private function ensureDictionaryRoutesAreLoaded(): void
    {
        $apiRoutesPath = base_path('routes/api.php');
        $includeLine = "require __DIR__.'/api/dictionaries.php';";

        $content = File::get($apiRoutesPath);

        if (! Str::contains($content, $includeLine)) {
            File::append($apiRoutesPath, PHP_EOL.$includeLine.PHP_EOL);
            $this->info('Dodano załączanie routes/api/dictionaries.php w routes/api.php.');
        }
    }

    private function appendDictionaryRoute(string $routeSlug, string $routeName, string $controllerClass): void
    {
        $routesPath = $this->dictionaryRoutesPath();

        if (! File::exists($routesPath)) {
            $this->error('Brak pliku routes/api/dictionaries.php. Przerwano dodawanie trasy.');

            return;
        }

        $entry = "        Route::apiResource('{$routeSlug}', \\App\\Http\\Controllers\\Dictionaries\\{$controllerClass}::class)".PHP_EOL
            ."            ->names('{$routeName}')".PHP_EOL
            ."            ->parameter('{$routeSlug}', 'dictionary');".PHP_EOL;

        $routes = File::get($routesPath);

        if (Str::contains($routes, $controllerClass.'::class')) {
            $this->info('Trasa dla tego słownika już istnieje.');

            return;
        }

        $placeholder = '// Dictionary API routes';

        if (Str::contains($routes, $placeholder)) {
            $routes = str_replace($placeholder, $placeholder.PHP_EOL.$entry, $routes);
        } else {
            $replacementsCount = 0;
            $routes = preg_replace('/(\s*\}\);\s*)$/', PHP_EOL.$entry.'$1', $routes, 1, $replacementsCount);

            if ($replacementsCount === 0) {
                $routes .= PHP_EOL.$entry;
            }
        }

        File::put($routesPath, $routes);
        $this->info('Dodano trasę w routes/api/dictionaries.php.');
    }

    private function dictionaryRoutesPath(): string
    {
        return base_path('routes/api/dictionaries.php');
    }
}
