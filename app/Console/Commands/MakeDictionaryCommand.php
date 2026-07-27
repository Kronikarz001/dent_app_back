<?php

namespace App\Console\Commands;

use App\Exceptions\DictionaryGenerationException;
use App\Services\DictionaryGeneratorService;
use Illuminate\Console\Command;

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
     * @param DictionaryGeneratorService $generator
     * @return int
     */
    public function handle(DictionaryGeneratorService $generator): int
    {
        try {
            $messages = $generator->generate((string) $this->option('name'));
        } catch (DictionaryGenerationException $exception) {
            $this->error($exception->getMessage());

            return 1;
        }

        foreach ($messages as $message) {
            $this->info($message);
        }

        return 0;
    }
}
