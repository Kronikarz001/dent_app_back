<?php

namespace Tests\Unit\Services;

use App\Dto\PageableDto;
use App\Exports\ExportInterface;
use App\Http\Requests\ExportRequest;
use App\Mappers\ExportMapper;
use App\Services\ExportService;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Facades\Excel;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ExportServiceTest extends TestCase
{
    private MockInterface $exportMapper;
    private ExportService $exportService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->exportMapper  = Mockery::mock(ExportMapper::class);
        $this->exportService = new ExportService($this->exportMapper);
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
    public function testExportDownloadsFileWithCorrectName(): void
    {
        Excel::fake();

        $request = Mockery::mock(ExportRequest::class);
        $export  = Mockery::mock(ExportInterface::class);
        $model   = Mockery::mock(Model::class);

        $dto = Mockery::mock(PageableDto::class);
        $dto->shouldReceive('setPerPage')->once()->with(-1);

        $request->shouldReceive('getDto')->once()->andReturn($dto);
        $request->type = 'xlsx';
        $request->name = 'test_export';

        $model->shouldReceive('getTable')->andReturn('patients');

        $this->exportMapper
            ->shouldReceive('map')
            ->once()
            ->with('xlsx')
            ->andReturn(\Maatwebsite\Excel\Excel::XLSX);

        $this->exportService->export($request, $export, $model);

        Excel::assertDownloaded('test_export.xlsx');
    }

    /**
     * @return void
     */
    public function testExportUsesTableNameWhenNoNameProvided(): void
    {
        Excel::fake();

        $request = Mockery::mock(ExportRequest::class);
        $export  = Mockery::mock(ExportInterface::class);
        $model   = Mockery::mock(Model::class);

        $dto = Mockery::mock(PageableDto::class);
        $dto->shouldReceive('setPerPage')->once()->with(-1);

        $request->shouldReceive('getDto')->once()->andReturn($dto);
        $request->type = 'csv';
        $request->name = null;

        $model->shouldReceive('getTable')->andReturn('patients');

        $this->exportMapper
            ->shouldReceive('map')
            ->once()
            ->with('csv')
            ->andReturn(\Maatwebsite\Excel\Excel::CSV);

        $this->exportService->export($request, $export, $model);

        Excel::assertDownloaded('patients.csv');
    }
}
