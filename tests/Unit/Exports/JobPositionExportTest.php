<?php

namespace Tests\Unit\Exports;

use App\Exports\JobPositionExport;
use App\Models\JobPosition;
use Tests\Unit\UnitTestCase;

/**
 * Summary of JobPositionExportTest
 */
final class JobPositionExportTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testHeadingsAreCorrect(): void
    {
        $export   = new JobPositionExport(collect());
        $expected = [
            'Nazwa stanowiska',
            'Nazwa r.żeński',
            'Nazwa r.męski',
        ];
        $this->assertEquals($expected, $export->headings());
    }

    /**
     * @return void
     */
    public function testMapWithAllValuesReturnsExpectedArray(): void
    {
        $jobPosition = new JobPosition([
            'name'   => 'Lekarz',
            'f_name' => 'Lekarka',
            'm_name' => 'Lekarz',
        ]);

        $export = new JobPositionExport(collect());
        $result = $export->map($jobPosition);

        $this->assertEquals([
            'name'   => 'Lekarz',
            'f_name' => 'Lekarka',
            'm_name' => 'Lekarz',
        ], $result);
    }

    /**
     * @return void
     */
    public function testCollectionReturnsJobPositionsCollection(): void
    {
        $jobPosition1 = new JobPosition(['name' => 'Lekarz']);
        $jobPosition2 = new JobPosition(['name' => 'Pielęgniarka']);
        $jobPositions = collect([$jobPosition1, $jobPosition2]);
        $export       = new JobPositionExport($jobPositions);

        $collection = $export->collection();
        $this->assertSame($jobPositions, $collection);
        $this->assertCount(2, $collection);
        $this->assertSame($jobPosition1, $collection[0]);
        $this->assertSame($jobPosition2, $collection[1]);
    }
}
