<?php

namespace Tests\Unit\Repositories;

use App\Models\Dictionary;
use App\Repositories\DictionaryRepository;
use App\Search\DictionarySearch;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Summary of DictionaryRepositoryTestEntry
 */
class DictionaryRepositoryTestEntry extends Dictionary {}

/**
 * Summary of DictionaryRepositoryTestSearch
 */
class DictionaryRepositoryTestSearch extends DictionarySearch
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return DictionaryRepositoryTestEntry::class;
    }
}

/**
 * Summary of DictionaryRepositoryTestConcrete
 */
class DictionaryRepositoryTestConcrete extends DictionaryRepository
{
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return DictionaryRepositoryTestEntry::class;
    }
}

/**
 * Summary of DictionaryRepositoryTest
 */
class DictionaryRepositoryTest extends TestCase
{
    /**
     * @var DictionaryRepositoryTestConcrete
     */
    private DictionaryRepositoryTestConcrete $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new DictionaryRepositoryTestConcrete(app(DictionaryRepositoryTestSearch::class));
    }

    /**
     * @return void
     */
    public function testCreatePersistsEntryUnderConcreteModelClass(): void
    {
        $entry = $this->repository->create(['key' => 'foo', 'value' => 'bar']);

        $this->assertInstanceOf(DictionaryRepositoryTestEntry::class, $entry);
        $this->assertDatabaseHas('dictionaries', ['uuid' => $entry->uuid, 'type' => DictionaryRepositoryTestEntry::class]);
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsMatchingEntry(): void
    {
        $entry = $this->repository->create(['key' => 'foo']);

        $this->assertTrue($this->repository->findByUuid($entry->uuid)->is($entry));
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsNullWhenMissing(): void
    {
        $this->assertNull($this->repository->findByUuid((string) Str::uuid()));
    }

    /**
     * @return void
     */
    public function testUpdateChangesEntryAttributes(): void
    {
        $entry = $this->repository->create(['key' => 'foo', 'value' => 'bar']);

        $updated = $this->repository->update($entry, ['value' => 'changed']);

        $this->assertSame('changed', $updated->value);
    }
}
