<?php

namespace Tests\Unit\Exports;

use App\Exports\PatientExport;
use App\Models\Patient;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PatientExportTest
 */
final class PatientExportTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testHeadingsAreCorrect(): void
    {
        $export = new PatientExport(collect());
        $expected = [
            'Imię',
            'Nazwisko',
            'Email',
        ];
        $this->assertEquals($expected, $export->headings());
    }

    /**
     * @return void
     */
    public function testMapWithAllValuesReturnsExpectedArray(): void
    {
        $patient = new Patient([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
            'pesel' => '12345678901',
        ]);

        $export = new PatientExport(collect());
        $result = $export->map($patient);

        $this->assertEquals([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
        ], $result);
    }

    /**
     * @return void
     */
    public function testCollectionReturnsPatientsCollection(): void
    {
        $patient1 = new Patient(['first_name' => 'Jan']);
        $patient2 = new Patient(['first_name' => 'Adam']);
        $patients = collect([$patient1, $patient2]);
        $export = new PatientExport($patients);

        $collection = $export->collection();
        $this->assertSame($patients, $collection);
        $this->assertCount(2, $collection);
        $this->assertSame($patient1, $collection[0]);
        $this->assertSame($patient2, $collection[1]);
    }
}
