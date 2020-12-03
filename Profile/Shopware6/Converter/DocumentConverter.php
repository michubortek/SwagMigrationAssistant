<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Profile\Shopware6\Converter;

use SwagMigrationAssistant\Migration\Converter\ConvertStruct;
use SwagMigrationAssistant\Migration\DataSelection\DefaultEntities;
use SwagMigrationAssistant\Profile\Shopware6\DataSelection\DataSet\DocumentDataSet;
use SwagMigrationAssistant\Profile\Shopware6\Logging\Log\UnsupportedDocumentTypeLog;

abstract class DocumentConverter extends ShopwareMediaConverter
{
    public function getMediaUuids(array $converted): ?array
    {
        $mediaIds = [];
        foreach ($converted as $document) {
            if (isset($document['documentMediaFile']['id'])) {
                $mediaIds[] = $document['documentMediaFile']['id'];
            }
        }

        return $mediaIds;
    }

    protected function convertData(array $data): ConvertStruct
    {
        $converted = $data;

        $this->mainMapping = $this->getOrCreateMappingMainCompleteFacade(
            DefaultEntities::ORDER_DOCUMENT,
            $data['id'],
            $converted['id']
        );

        $converted['documentTypeId'] = $this->mappingService->getDocumentTypeUuid($converted['documentType']['technicalName'], $this->context, $this->migrationContext);
        if ($converted['documentTypeId'] === null) {
            $this->loggingService->addLogEntry(new UnsupportedDocumentTypeLog($this->runId, DefaultEntities::ORDER_DOCUMENT, $data['id'], $data['documentType']['technicalName']));

            return new ConvertStruct(null, $data, $this->mainMapping['id'] ?? null);
        }
        unset($converted['documentType']);

        if (isset($converted['config']['documentTypeId'])) {
            $converted['config']['documentTypeId'] = $converted['documentTypeId'];
        }

        if (isset($converted['documentMediaFile'])) {
            $this->updateMediaAssociation($converted['documentMediaFile'], DocumentDataSet::getEntity());
        }

        return new ConvertStruct($converted, null, $this->mainMapping['id'] ?? null);
    }
}
