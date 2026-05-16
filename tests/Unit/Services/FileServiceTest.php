<?php

namespace Tests\Unit\Services;

use App\DTO\FileDto;
use App\Enums\FileableType;
use App\Exceptions\FileUploadException;
use App\Models\File;
use App\Models\UuidModel;
use App\Models\User;
use App\Repositories\FileRepositoryInterface;
use App\Services\FileService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\Mocks\Repositories\FileMockRepository;
use Tests\Unit\UnitTestCase;

class FileServiceTest extends UnitTestCase
{
    /**
     * @var FilesystemAdapter|(FilesystemAdapter&Mockery\MockInterface&object&Mockery\LegacyMockInterface)|(Mockery\MockInterface&object&Mockery\LegacyMockInterface)
     */
    protected FilesystemAdapter $diskMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->diskMock = Mockery::mock(FilesystemAdapter::class);

        Storage::shouldReceive('disk')
            ->with('local')
            ->andReturn($this->diskMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @return void
     * @throws FileNotFoundException
     */
    public function testGetFileReturnsBase64EncodedArray(): void
    {
        $path = 'registry_rows/test.txt';
        $filename = 'test.txt';
        $mimetype = 'text/plain';
        $rawBody = 'hello world';

        $fileModel = new File([
            'path' => $path,
            'filename' => $filename,
            'mimetype' => $mimetype,
        ]);

        $this->diskMock
            ->shouldReceive('exists')
            ->once()
            ->with($path)
            ->andReturnTrue();

        $this->diskMock
            ->shouldReceive('get')
            ->once()
            ->with($path)
            ->andReturn(Crypt::encrypt($rawBody));

        $this->diskMock
            ->shouldReceive('mimeType')
            ->with($path)
            ->andReturn($mimetype);

        $service = new FileService(new FileMockRepository(), $this->diskMock);

        $result = $service->getFile($fileModel);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('mime', $result);
        $this->assertArrayHasKey('content', $result);

        $this->assertSame($filename, $result['filename']);
        $this->assertSame($mimetype, $result['mime']);
        $this->assertSame(base64_encode($rawBody), $result['content']);
    }

    /**
     * @return void
     */
    public function testGetAllFilesCallsRepositoryWithCorrectParams(): void
    {
        $uuidModel = Mockery::mock(UuidModel::class);
        $paginator = new LengthAwarePaginator([], 0, 15);

        $uuidModel
            ->shouldReceive('getAttribute')
            ->once()
            ->with('uuid')
            ->andReturn('1234');

        $repoMock = Mockery::mock(FileRepositoryInterface::class);
        $repoMock
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->with(Mockery::any())
            ->andReturn($paginator);

        $service = new FileService($repoMock, $this->diskMock);
        $this->assertSame($paginator, $service->getAllFiles($uuidModel));
    }

    /**
     * @return void
     * @throws FileNotFoundException
     */
    public function testGetFileThrowsWhenFileDoesNotExist(): void
    {
        $path = 'noexist.txt';
        $fileModel = new File(['path' => $path]);

        $this->diskMock
            ->shouldReceive('exists')
            ->once()
            ->with($path)
            ->andReturnFalse();

        $service = new FileService(new FileMockRepository(), $this->diskMock);

        $this->expectException(FileNotFoundException::class);

        $service->getFile($fileModel);
    }

    /**
     * @return void
     */
    public function testSaveFilePersistsCorrectData(): void
    {
        $dtoFile = Mockery::mock(UploadedFile::class);
        $dtoFile->shouldReceive('getClientOriginalName')->andReturn('orig.pdf');
        $dtoFile->shouldReceive('getClientOriginalExtension')->andReturn('pdf');
        $dtoFile->shouldReceive('getSize')->andReturn(42);
        $dtoFile->shouldReceive('getMimeType')->andReturn('application/pdf');
        $dtoFile->shouldReceive('getContent')->andReturn('xyz');

        $model = new class extends UuidModel {
            public string $uuid = 'abcd';

            public function getMorphClass(): string
            {
                return User::class;
            }
        };

        $repoMock = Mockery::mock(FileRepositoryInterface::class);
        $repoMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data) {
                return $data['fileable_id'] === 'abcd'
                    && $data['fileable_type'] === User::class;
            }))
            ->andReturn(new File(['path' => 'some/path.pdf']));

        $this->diskMock
            ->shouldReceive('put')
            ->once()
            ->andReturn('some/path.pdf');

        $service = new FileService($repoMock, $this->diskMock);
        $result = $service->saveFile(
            new FileDto([$dtoFile], FileableType::USER),
            $model
        );

        $this->assertSame('some/path.pdf', $result[0]->path);
    }

    /**
     * @return void
     * @throws FileNotFoundException
     */
    public function testDeleteFile(): void
    {
        $repoMock = Mockery::mock(FileRepositoryInterface::class);
        $file = new File(['path' => 'some/path.pdf']);
        $repoMock
            ->shouldReceive('delete')
            ->once()
            ->with($file)
            ->andReturn(true);

        $this->diskMock->shouldReceive('exists')
            ->once()
            ->with('some/path.pdf')
            ->andReturnTrue();

        $this->diskMock->shouldReceive('delete')
            ->once()
            ->with('some/path.pdf')
            ->andReturn(true);

        $service = new FileService($repoMock, $this->diskMock);

        $response = $service->deleteFile($file);

        $this->assertTrue($response);
    }

    /**
     * @return void
     * @throws FileNotFoundException
     */
    public function testDeleteFileThrowsWhenFileDoesNotExist(): void
    {
        $repoMock = Mockery::mock(FileRepositoryInterface::class);
        $file = new File(['path' => 'some/path.pdf']);
        $repoMock
            ->shouldReceive('delete')
            ->never()
            ->with($file)
            ->andReturn(true);

        $this->diskMock->shouldReceive('exists')
            ->once()
            ->with('some/path.pdf')
            ->andReturnFalse();

        $this->diskMock->shouldReceive('delete')
            ->never()
            ->with('some/path.pdf')
            ->andReturn(true);

        $service = new FileService($repoMock, $this->diskMock);

        $this->expectException(FileNotFoundException::class);

        $service->deleteFile($file);
    }

    /**
     * @return void
     */
    public function testUpdateFile(): void
    {
        $repoMock = Mockery::mock(FileRepositoryInterface::class);

        $existingFileModel = new File([
            'filename' => 'old_filename.txt'
        ]);

        $updatedFileModel = new File([
            'filename' => 'new_filename.txt'
        ]);

        $repoMock->shouldReceive('update')
            ->once()
            ->with($existingFileModel, Mockery::any())
            ->andReturn($updatedFileModel);

        $service = new FileService($repoMock, $this->diskMock);

        $file = $service->updateFileName($existingFileModel, "new_filename.txt");

        $this->assertSame($updatedFileModel, $file);
    }

    /**
     * @return void
     * @throws FileUploadException
     */
    public function testSaveFileThrowsExceptionWhenDiskPutFails(): void
    {
        $this->expectException(FileUploadException::class);

        $dtoFile = Mockery::mock(UploadedFile::class);
        $dtoFile->shouldReceive('getClientOriginalName')->andReturn('test.pdf');
        $dtoFile->shouldReceive('getClientOriginalExtension')->andReturn('pdf');
        $dtoFile->shouldReceive('getSize')->andReturn(100);
        $dtoFile->shouldReceive('getMimeType')->andReturn('application/pdf');
        $dtoFile->shouldReceive('getContent')->andReturn('content');

        $model = new class extends UuidModel {
            public string $uuid = 'test-uuid';

            public function getMorphClass(): string
            {
                return User::class;
            }
        };

        $this->diskMock
            ->shouldReceive('put')->once()
            ->andReturnFalse();

        $service = new FileService(new FileMockRepository(), $this->diskMock);
        $service->saveFile(
            new FileDto([$dtoFile], FileableType::USER),
            $model
        );
    }

    /**
     * @return void
     */
    public function testUpdateFileNameCallsRepositoryUpdate(): void
    {
        $existing = new File(['uuid' => 'abc', 'filename' => 'old']);
        $updated = new File(['uuid' => 'abc', 'filename' => 'new']);
        $repoMock = Mockery::mock(FileRepositoryInterface::class);

        $repoMock
            ->shouldReceive('update')->once()
            ->with($existing, ['filename' => 'new'])
            ->andReturn($updated);

        $service = new FileService($repoMock, $this->diskMock);
        $result = $service->updateFileName($existing, 'new');

        $this->assertSame($updated, $result);
    }

    /**
     * @return void
     * @throws FileNotFoundException
     */
    public function testDeleteFileDeletesChildrenAndParentAndCallsRepositoryDelete(): void
    {
        $child1 = new File(['path' => 'c1.txt']);
        $child2 = new File(['path' => 'c2.txt']);
        $parent = new File(['path' => 'p.txt']);
        $parent->setRelation('files', new Collection([$child1, $child2]));

        foreach (['c1.txt', 'c2.txt', 'p.txt'] as $path) {
            $this->diskMock
                ->shouldReceive('exists')->once()
                ->with($path)->andReturnTrue();
            $this->diskMock
                ->shouldReceive('delete')->once()
                ->with($path);
        }

        $repoMock = Mockery::mock(FileRepositoryInterface::class);
        $repoMock
            ->shouldReceive('delete')->once()
            ->with($parent)->andReturnTrue();

        $service = new FileService($repoMock, $this->diskMock);
        $result = $service->deleteFile($parent);

        $this->assertTrue($result);
    }
}
