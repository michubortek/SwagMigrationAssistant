<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Profile\Shopware\Media;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Media\MediaException;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagMigrationAssistant\Migration\DataSelection\DefaultEntities;
use SwagMigrationAssistant\Migration\Logging\Log\CannotGetFileRunLog;
use SwagMigrationAssistant\Migration\Logging\Log\ExceptionRunLog;
use SwagMigrationAssistant\Migration\Logging\LoggingServiceInterface;
use SwagMigrationAssistant\Migration\Media\MediaFileProcessorInterface;
use SwagMigrationAssistant\Migration\Media\MediaProcessWorkloadStruct;
use SwagMigrationAssistant\Migration\Media\Processor\BaseMediaService;
use SwagMigrationAssistant\Migration\MigrationContextInterface;
use SwagMigrationAssistant\Profile\Shopware\DataSelection\DataSet\ProductDownloadDataSet;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Local\ShopwareLocalGateway;
use SwagMigrationAssistant\Profile\Shopware\ShopwareProfileInterface;

#[Package('services-settings')]
class LocalProductDownloadProcessor extends BaseMediaService implements MediaFileProcessorInterface
{
    public function __construct(
        EntityRepository $mediaFileRepo,
        private readonly MediaService $mediaService,
        private readonly LoggingServiceInterface $loggingService,
        Connection $dbalConnection
    ) {
        parent::__construct($dbalConnection, $mediaFileRepo);
    }

    public function supports(MigrationContextInterface $migrationContext): bool
    {
        if ($migrationContext->getDataSet() === null) {
            return false;
        }

        return $migrationContext->getProfile() instanceof ShopwareProfileInterface
            && $migrationContext->getGateway()->getName() === ShopwareLocalGateway::GATEWAY_NAME
            && $migrationContext->getDataSet()::getEntity() === ProductDownloadDataSet::getEntity();
    }

    public function process(
        MigrationContextInterface $migrationContext,
        Context $context,
        array $workload,
        int $fileChunkByteSize
    ): array {
        $mappedWorkload = [];
        foreach ($workload as $work) {
            $mappedWorkload[$work->getMediaId()] = $work;
        }

        $media = $this->getMediaFiles(\array_keys($mappedWorkload), $migrationContext->getRunUuid());

        return $this->copyMediaFiles($media, $mappedWorkload, $migrationContext, $context);
    }

    private function getInstallationRoot(MigrationContextInterface $migrationContext): string
    {
        $connection = $migrationContext->getConnection();
        if ($connection === null) {
            return '';
        }

        $credentials = $connection->getCredentialFields();

        if ($credentials === null) {
            return '';
        }

        return $credentials['installationRoot'] ?? '';
    }

    /**
     * @param MediaProcessWorkloadStruct[] $mappedWorkload
     *
     * @return MediaProcessWorkloadStruct[]
     */
    private function copyMediaFiles(
        array $media,
        array $mappedWorkload,
        MigrationContextInterface $migrationContext,
        Context $context
    ): array {
        $installationRoot = $this->getInstallationRoot($migrationContext);
        $processedMedia = [];
        $failedMedia = [];

        foreach ($media as $mediaFile) {
            $sourcePath = $installationRoot . '/files/' . $mediaFile['uri'];
            $mediaId = $mediaFile['media_id'];

            if (!\file_exists($sourcePath)) {
                $mappedWorkload[$mediaId]->setState(MediaProcessWorkloadStruct::ERROR_STATE);
                $this->loggingService->addLogEntry(new CannotGetFileRunLog(
                    $mappedWorkload[$mediaId]->getRunId(),
                    DefaultEntities::PRODUCT_DOWNLOAD,
                    $mediaId,
                    $sourcePath
                ));
                $processedMedia[] = $mediaId;
                $failedMedia[] = $mediaId;

                continue;
            }

            $mappedWorkload[$mediaId]->setState(MediaProcessWorkloadStruct::FINISH_STATE);

            try {
                $this->persistFileToMedia($sourcePath, $mediaFile, $context);

                $processedMedia[] = $mediaId;
            } catch (\Exception $e) {
                $failedMedia[] = $mediaId;

                $mappedWorkload[$mediaId]->setState(MediaProcessWorkloadStruct::ERROR_STATE);

                $this->loggingService->addLogEntry(new ExceptionRunLog(
                    $mappedWorkload[$mediaId]->getRunId(),
                    DefaultEntities::PRODUCT_DOWNLOAD,
                    $e,
                    $mediaId
                ));
            }
        }

        $this->setProcessedFlag($migrationContext->getRunUuid(), $context, $processedMedia, $failedMedia);
        $this->loggingService->saveLogging($context);

        return \array_values($mappedWorkload);
    }

    private function persistFileToMedia(
        string $sourcePath,
        array $media,
        Context $context
    ): void {
        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($sourcePath, $media): void {
            $fileExtension = \pathinfo($sourcePath, \PATHINFO_EXTENSION);
            $mimeType = \mime_content_type($sourcePath);
            $streamContext = \stream_context_create([
                'http' => [
                    'follow_location' => 0,
                    'max_redirects' => 0,
                ],
            ]);
            $fileBlob = \file_get_contents($sourcePath, false, $streamContext);
            $name = \preg_replace('/\\.[^.\\s]{3,4}$/', '', $media['file_name']);
            $name = \preg_replace('/[^a-zA-Z0-9_-]+/', '-', \mb_strtolower($name)) ?? Uuid::randomHex();

            if ($fileBlob === false || $mimeType === false) {
                throw new \RuntimeException(\sprintf('Could read file %s.', $sourcePath));
            }

            try {
                $this->mediaService->saveFile(
                    $fileBlob,
                    $fileExtension,
                    $mimeType,
                    $name,
                    $context,
                    'product_download',
                    $media['media_id']
                );
            } catch (MediaException $mediaException) {
                if ($mediaException->getErrorCode() === MediaException::MEDIA_DUPLICATED_FILE_NAME) {
                    $this->mediaService->saveFile(
                        $fileBlob,
                        $fileExtension,
                        $mimeType,
                        $name . \mb_substr(Uuid::randomHex(), 0, 5),
                        $context,
                        'product_download',
                        $media['media_id']
                    );
                } elseif (\in_array($mediaException->getErrorCode(), [MediaException::MEDIA_ILLEGAL_FILE_NAME, MediaException::MEDIA_EMPTY_FILE_NAME], true)) {
                    $this->mediaService->saveFile(
                        $fileBlob,
                        $fileExtension,
                        $mimeType,
                        $media['media_id'],
                        $context,
                        'product_download',
                        $media['media_id']
                    );
                }
            }
        });
    }
}
