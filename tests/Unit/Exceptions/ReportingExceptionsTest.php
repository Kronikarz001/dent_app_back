<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\FileUploadException;
use App\Exceptions\P1IntegrationNotImplementedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tests\Unit\UnitTestCase;

/**
 * Summary of ReportingExceptionsTest
 */
final class ReportingExceptionsTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testFileUploadExceptionReportsAsError(): void
    {
        Log::shouldReceive('error')->once()->with('Disk full');

        (new FileUploadException('Disk full'))->report();
    }

    /**
     * @return void
     */
    public function testFileUploadExceptionAcceptsCustomStatus(): void
    {
        $exception = new FileUploadException('Too large', 413);

        $this->assertSame(413, $exception->render(Request::create('/'))->getStatusCode());
    }

    /**
     * @return void
     */
    public function testP1IntegrationNotImplementedExceptionReportsAsWarning(): void
    {
        Log::shouldReceive('warning')->once()->with('Integracja z P1 nie jest jeszcze podłączona.');

        (new P1IntegrationNotImplementedException)->report();
    }

    /**
     * @return void
     */
    public function testP1IntegrationNotImplementedExceptionAcceptsCustomStatus(): void
    {
        $exception = new P1IntegrationNotImplementedException('P1 niedostępne', 503);

        $this->assertSame(503, $exception->render(Request::create('/'))->getStatusCode());
    }
}
