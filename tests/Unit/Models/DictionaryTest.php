<?php

namespace Tests\Unit\Models;

use App\Models\Dictionary;
use Tests\Unit\UnitTestCase;

/**
 * Summary of DictionaryTestEntryA
 */
class DictionaryTestEntryA extends Dictionary {}

/**
 * Summary of DictionaryTestEntryB
 */
class DictionaryTestEntryB extends Dictionary {}

/**
 * Summary of DictionaryTest
 */
final class DictionaryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatingSetsTypeToConcreteClassName(): void
    {
        $entry = DictionaryTestEntryA::create(['key' => 'foo', 'value' => 'bar']);

        $this->assertSame(DictionaryTestEntryA::class, $entry->type);
    }

    /**
     * @return void
     */
    public function testTypeScopeIsolatesRowsPerConcreteClass(): void
    {
        DictionaryTestEntryA::create(['key' => 'a', 'value' => '1']);
        DictionaryTestEntryB::create(['key' => 'b', 'value' => '2']);

        $this->assertCount(1, DictionaryTestEntryA::all());
        $this->assertCount(1, DictionaryTestEntryB::all());
    }

    /**
     * @return void
     */
    public function testAdditionalIsDecodedToObjectOnRetrieval(): void
    {
        $entry = DictionaryTestEntryA::create(['key' => 'foo', 'additional' => ['nested' => 'value']]);

        $retrieved = DictionaryTestEntryA::find($entry->uuid);

        $this->assertIsObject($retrieved->additional);
        $this->assertSame('value', $retrieved->additional->nested);
    }
}
