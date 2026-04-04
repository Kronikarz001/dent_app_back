<?php

namespace Tests\Unit\Export;

use App\Exports\UserExport;
use App\Models\User;
use Tests\Unit\UnitTestCase;

/**
 * Summary of UserExportTest
 */
final class UserExportTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testHeadingsAreCorrect(): void
    {
        $export   = new UserExport(collect());
        $expected = [
            'Imię',
            'Nazwisko',
            'Email',
            'Email prywatny',
            'PESEL',
        ];
        $this->assertEquals($expected, $export->headings());
    }

    /**
     * @return void
     */
    public function testMapWithAllValuesReturnsExpectedArray(): void
    {
        $user = new User([
            'first_name'    => 'Jan',
            'last_name'     => 'Kowalski',
            'email'         => 'jan@example.com',
            'private_email' => 'jan.priv@example.com',
            'pesel'         => '12345678901',
        ]);

        $export = new UserExport(collect());
        $result = $export->map($user);

        $this->assertEquals([
            'first_name'    => 'Jan',
            'last_name'     => 'Kowalski',
            'email'         => 'jan@example.com',
            'private_email' => 'jan.priv@example.com',
            'pesel'         => '12345678901',
        ], $result);
    }

    /**
     * @return void
     */
    public function testCollectionReturnsUsersCollection(): void
    {
        $user1 = new User(['first_name' => 'Jan']);
        $user2 = new User(['first_name' => 'Adam']);
        $users = collect([$user1, $user2]);
        $export = new UserExport($users);

        $collection = $export->collection();
        $this->assertSame($users, $collection);
        $this->assertCount(2, $collection);
        $this->assertSame($user1, $collection[0]);
        $this->assertSame($user2, $collection[1]);
    }
}
