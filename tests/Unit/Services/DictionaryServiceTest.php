<?php

namespace Tests\Unit\Services;

use App\Models\Dictionary;
use App\Repositories\DictionaryRepositoryInterface;
use App\Services\DictionaryService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Summary of DictionaryServiceTest
 */
class DictionaryServiceTest extends TestCase
{
    private MockInterface $repository;

    private DictionaryService $service;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(DictionaryRepositoryInterface::class);
        $this->service = new DictionaryService;
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testCreateDictionaryMergesTypeAndDelegatesToRepository(): void
    {
        $dictionary = Mockery::mock(Dictionary::class);
        $type = 'App\\Models\\Dictionaries\\SampleDictionary';

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with(['key' => 'k', 'value' => 'v', 'type' => $type])
            ->andReturn($dictionary);

        $result = $this->service->createDictionary($this->repository, ['key' => 'k', 'value' => 'v'], $type);

        $this->assertSame($dictionary, $result);
    }

    /**
     * @return void
     */
    public function testUpdateDictionaryDelegatesToRepository(): void
    {
        $dictionary = Mockery::mock(Dictionary::class);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with($dictionary, ['value' => 'nowa'])
            ->andReturn($dictionary);

        $result = $this->service->updateDictionary($this->repository, $dictionary, ['value' => 'nowa']);

        $this->assertSame($dictionary, $result);
    }
}
