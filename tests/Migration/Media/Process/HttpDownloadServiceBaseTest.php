<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Test\Migration\Media\Process;

use Doctrine\DBAL\Connection;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\QueryBuilder;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Test\Stub\DataAbstractionLayer\StaticEntityRepository;
use SwagMigrationAssistant\Migration\Gateway\HttpClientInterface;
use SwagMigrationAssistant\Migration\Media\MediaProcessWorkloadStruct;
use SwagMigrationAssistant\Migration\Media\SwagMigrationMediaFileCollection;
use SwagMigrationAssistant\Migration\Media\SwagMigrationMediaFileDefinition;
use SwagMigrationAssistant\Migration\MigrationContext;
use SwagMigrationAssistant\Profile\Shopware6\Shopware6MajorProfile;
use SwagMigrationAssistant\Test\Mock\Migration\Logging\DummyLoggingService;

#[Package('services-settings')]
class HttpDownloadServiceBaseTest extends TestCase
{
    private string $runId;

    private MigrationContext $migrationContext;

    private Context $context;

    private DummyLoggingService $loggingService;

    protected function setUp(): void
    {
        $this->runId = Uuid::randomHex();
        $this->loggingService = new DummyLoggingService();
        $this->migrationContext = new MigrationContext(
            new Shopware6MajorProfile('6.6.0'),
            null,
            Uuid::randomHex(),
            null,
            0,
            100
        );
        $this->context = Context::createDefaultContext();
    }

    public function testSupports(): void
    {
        $httpDownloadServiceBase = $this->createBase([]);
        static::assertTrue($httpDownloadServiceBase->supports($this->migrationContext));
    }

    public function testProcessSucceeds(): void
    {
        $testResult = [];

        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->method('andWhere')
            ->willReturnCallback(function ($argument) use (&$testResult, $queryBuilderMock) {
                $testResult[] = $argument;

                return $queryBuilderMock;
            });

        $mediaFiles = [
            [
                'mediaId' => Uuid::randomHex(),
                'fileName' => 'test.jpg',
                'fileContent' => 'hello world',
                'uri' => 'http://test.localhost/test.jpg?random=123456789',
            ],
        ];
        $httpDownloadServiceBase = $this->createBase($mediaFiles, $queryBuilderMock);
        $initialWorkload = [
            new MediaProcessWorkloadStruct(
                $mediaFiles[0]['mediaId'],
                $this->runId,
            ),
        ];
        $resultWorkload = $httpDownloadServiceBase->process($this->migrationContext, $this->context, $initialWorkload);

        static::assertEquals([
            new MediaProcessWorkloadStruct(
                $mediaFiles[0]['mediaId'],
                $this->runId,
                MediaProcessWorkloadStruct::FINISH_STATE,
                [
                    'file_size' => 11,
                    'uri' => 'http://test.localhost/test.jpg?random=123456789',
                ],
                0,
                11,
            ),
        ], $resultWorkload);

        static::assertEquals([], $this->loggingService->getLoggingArray());
        static::assertContains('media_id IN (:ids)', $testResult);
        static::assertContains('written = 1', $testResult);
    }

    public function testProcessWithRequestFailure(): void
    {
        $mediaFiles = [
            [
                'mediaId' => Uuid::randomHex(),
                'fileName' => 'test.jpg',
                'fileContent' => null,
                'uri' => 'http://test.localhost/test.jpg?random=123456789',
            ],
        ];
        $httpDownloadServiceBase = $this->createBase($mediaFiles);
        $initialWorkload = [
            new MediaProcessWorkloadStruct(
                $mediaFiles[0]['mediaId'],
                $this->runId,
            ),
        ];

        $resultWorkload = $httpDownloadServiceBase->process($this->migrationContext, $this->context, $initialWorkload);
        static::assertEquals([
            new MediaProcessWorkloadStruct(
                $mediaFiles[0]['mediaId'],
                $this->runId,
                MediaProcessWorkloadStruct::FINISH_STATE,
                [
                    'file_size' => 0,
                    'uri' => 'http://test.localhost/test.jpg?random=123456789',
                ],
                1,
                0,
            ),
        ], $resultWorkload);
        static::assertEquals([], $this->loggingService->getLoggingArray());

        // second attempt
        $resultWorkload = $httpDownloadServiceBase->process($this->migrationContext, $this->context, $resultWorkload);
        static::assertEquals([
            new MediaProcessWorkloadStruct(
                $mediaFiles[0]['mediaId'],
                $this->runId,
                MediaProcessWorkloadStruct::FINISH_STATE,
                [
                    'file_size' => 0,
                    'uri' => 'http://test.localhost/test.jpg?random=123456789',
                ],
                2,
                0,
            ),
        ], $resultWorkload);
        static::assertEquals([], $this->loggingService->getLoggingArray());

        // third attempt
        $resultWorkload = $httpDownloadServiceBase->process($this->migrationContext, $this->context, $resultWorkload);
        static::assertEquals([
            new MediaProcessWorkloadStruct(
                $mediaFiles[0]['mediaId'],
                $this->runId,
                MediaProcessWorkloadStruct::FINISH_STATE,
                [
                    'file_size' => 0,
                    'uri' => 'http://test.localhost/test.jpg?random=123456789',
                ],
                3,
                0,
            ),
        ], $resultWorkload);
        static::assertEquals([], $this->loggingService->getLoggingArray());

        // fourth attempt
        $resultWorkload = $httpDownloadServiceBase->process($this->migrationContext, $this->context, $resultWorkload);
        static::assertEquals([
            new MediaProcessWorkloadStruct(
                $mediaFiles[0]['mediaId'],
                $this->runId,
                MediaProcessWorkloadStruct::ERROR_STATE,
                [
                    'file_size' => 0,
                    'uri' => 'http://test.localhost/test.jpg?random=123456789',
                ],
                4,
                0,
            ),
        ], $resultWorkload);
        static::assertEquals([
            [
                'level' => 'warning',
                'code' => 'SWAG_MIGRATION_CANNOT_GET_MEDIA_FILE',
                'title' => 'The media file cannot be downloaded / copied',
                'description' => 'The media file with the uri "' . $mediaFiles[0]['uri'] . '" and media id "' . $mediaFiles[0]['mediaId'] . '" cannot be downloaded / copied. The following request error occurred: Request failed',
                'parameters' => [
                    'entity' => 'media',
                    'sourceId' => $mediaFiles[0]['mediaId'],
                    'uri' => $mediaFiles[0]['uri'],
                ],
                'titleSnippet' => 'swag-migration.index.error.SWAG_MIGRATION_CANNOT_GET_FILE.title',
                'descriptionSnippet' => 'swag-migration.index.error.SWAG_MIGRATION_CANNOT_GET_FILE.description',
                'entity' => 'media',
                'sourceId' => $mediaFiles[0]['mediaId'],
                'runId' => $this->runId,
            ],
        ], $this->loggingService->getLoggingArray());
    }

    public function testProcessShouldCreateATempFile(): void
    {
        $mediaFiles = [
            $this->createFileData(__DIR__ . '/_fixtures/test1.jpg'),
            $this->createFileData(__DIR__ . '/_fixtures/test2.jpg'),
        ];

        $initialWorkload = [];
        $migrationMediaFiles = [];
        foreach ($mediaFiles as $media) {
            $initialWorkload[] = new MediaProcessWorkloadStruct(
                $media['mediaId'],
                $this->runId,
            );

            $migrationMediaFiles[] = [
                'id' => Uuid::randomBytes(),
                'run_id' => Uuid::fromHexToBytes($this->runId),
                'media_id' => Uuid::fromHexToBytes($media['mediaId']),
                'file_name' => $media['fileName'],
                'file_size' => $media['fileSize'],
                'uri' => $media['uri'],
            ];
        }

        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->method('andWhere')->willReturnSelf();
        $queryBuilderMock->method('fetchAllAssociative')->willReturn($migrationMediaFiles);

        $dbalConnection = $this->createMock(Connection::class);
        $dbalConnection->method('createQueryBuilder')->willReturn($queryBuilderMock);

        /** @var StaticEntityRepository<SwagMigrationMediaFileCollection> $mediaFileRepo */
        $mediaFileRepo = new StaticEntityRepository(
            [],
            new SwagMigrationMediaFileDefinition()
        );

        $fileSaverMock = $this->createMock(FileSaver::class);
        // TestCase: Check that the filename starts with "/tmp/", the file exists and the size is correct
        $fileSaverMock->expects(static::exactly(2))
            ->method('persistFileToMedia')
            ->willReturnCallback(function ($mediaFile, $destination, $mediaId): void {
                static::assertInstanceOf(MediaFile::class, $mediaFile);
                static::assertStringStartsWith('/tmp/', $mediaFile->getFileName());
                static::assertFileExists($mediaFile->getFileName());
                static::assertSame($mediaFile->getFileSize(), \filesize($mediaFile->getFileName()));
                static::assertEquals('jpg', $mediaFile->getFileExtension());

                static::assertIsString($destination);
                static::assertStringStartsWith('test', $destination);

                static::assertIsString($mediaId);
                static::assertTrue(Uuid::isValid($mediaId));
            });

        $httpDownloadServiceBase = new DummyHttpDownloadService(
            $dbalConnection,
            $mediaFileRepo,
            $fileSaverMock,
            $this->loggingService,
            $this->createHttpClientMock($mediaFiles)
        );

        $resultWorkload = $httpDownloadServiceBase->process($this->migrationContext, $this->context, $initialWorkload);
        foreach ($resultWorkload as $workload) {
            static::assertEquals(MediaProcessWorkloadStruct::FINISH_STATE, $workload->getState());
        }
    }

    /**
     * @param list<array{mediaId: string, fileName: string, fileContent: ?string, uri: string}> $migrationMedia
     */
    private function createBase(array $migrationMedia, ?QueryBuilder $queryBuilder = null): DummyHttpDownloadService
    {
        $migrationMediaFiles = [];
        foreach ($migrationMedia as $media) {
            $migrationMediaFiles[] = [
                'id' => Uuid::randomBytes(),
                'run_id' => Uuid::fromHexToBytes($this->runId),
                'media_id' => Uuid::fromHexToBytes($media['mediaId']),
                'file_name' => $media['fileName'],
                'file_size' => $media['fileContent'] === null ? 0 : \mb_strlen($media['fileContent']),
                'uri' => $media['uri'],
            ];
        }

        $dbalConnection = $this->createMock(Connection::class);
        if (!$queryBuilder instanceof QueryBuilder || !$queryBuilder instanceof MockObject) {
            $queryBuilder = $this->createMock(QueryBuilder::class);
        }

        $queryBuilder->method('fetchAllAssociative')->willReturn($migrationMediaFiles);
        $dbalConnection->method('createQueryBuilder')->willReturn($queryBuilder);

        /** @var StaticEntityRepository<SwagMigrationMediaFileCollection> $mediaFileRepo */
        $mediaFileRepo = new StaticEntityRepository(
            [],
            new SwagMigrationMediaFileDefinition()
        );
        $fileSaver = $this->createMock(FileSaver::class);
        $httpClient = $this->createHttpClientMock($migrationMedia);

        return new DummyHttpDownloadService(
            $dbalConnection,
            $mediaFileRepo,
            $fileSaver,
            $this->loggingService,
            $httpClient
        );
    }

    /**
     * @return array{mediaId: string, fileName: string, fileSize: int, fileContent: string, uri: string, path: string}
     */
    private function createFileData(string $filePath): array
    {
        $fileSize = \filesize($filePath);
        static::assertIsInt($fileSize);
        static::assertNotEmpty($fileSize);

        $fileHandle = \fopen($filePath, 'rb');
        static::assertIsResource($fileHandle);

        $fileContent = \fread($fileHandle, $fileSize);
        static::assertIsString($fileContent);

        \fclose($fileHandle);

        return [
            'mediaId' => Uuid::randomHex(),
            'fileName' => 'test1.jpg',
            'fileSize' => $fileSize,
            'fileContent' => $fileContent,
            'uri' => 'http://test.localhost/test1.jpg?random=' . \random_int(10000, 99999),
            'path' => $filePath,
        ];
    }

    /**
     * @param list<array<string, mixed>> $mediaFiles
     */
    private function createHttpClientMock(array $mediaFiles): HttpClientInterface
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('getAsync')->willReturnCallback(function ($uri) use ($mediaFiles) {
            $fileContent = null;
            foreach ($mediaFiles as $mediaFile) {
                if ($mediaFile['uri'] === $uri) {
                    $fileContent = $mediaFile['fileContent'];

                    break;
                }
            }

            $promise = new Promise();
            if ($fileContent) {
                $response = $this->createMock(ResponseInterface::class);
                $stream = $this->createMock(StreamInterface::class);
                $stream->method('getContents')->willReturn($fileContent);
                $response->method('getBody')->willReturn($stream);

                $promise->resolve($response);
            } else {
                $requestException = new RequestException(
                    'Request failed',
                    $this->createMock(RequestInterface::class),
                );

                $promise->reject($requestException);
            }

            return $promise;
        });

        return $httpClient;
    }
}
