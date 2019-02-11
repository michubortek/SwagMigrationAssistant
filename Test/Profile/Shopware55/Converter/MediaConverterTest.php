<?php declare(strict_types=1);

namespace SwagMigrationNext\Test\Profile\Shopware55\Converter;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Struct\Uuid;
use SwagMigrationNext\Migration\MigrationContext;
use SwagMigrationNext\Profile\Shopware55\Converter\ConverterHelperService;
use SwagMigrationNext\Profile\Shopware55\Converter\MediaConverter;
use SwagMigrationNext\Profile\Shopware55\Shopware55Profile;
use SwagMigrationNext\Test\Mock\Migration\Mapping\DummyMappingService;
use SwagMigrationNext\Test\Mock\Migration\Media\DummyMediaFileService;

class MediaConverterTest extends TestCase
{
    /**
     * @var MediaConverter
     */
    private $mediaConverter;

    /**
     * @var string
     */
    private $runId;

    /**
     * @var string
     */
    private $profileId;

    /**
     * @var MigrationContext
     */
    private $migrationContext;

    protected function setUp(): void
    {
        $mediaFileService = new DummyMediaFileService();
        $mappingService = new DummyMappingService();
        $converterHelperService = new ConverterHelperService();
        $this->mediaConverter = new MediaConverter($mappingService, $converterHelperService, $mediaFileService);

        $this->runId = Uuid::uuid4()->getHex();
        $this->profileId = Uuid::uuid4()->getHex();

        $this->migrationContext = new MigrationContext(
            $this->runId,
            $this->profileId,
            Shopware55Profile::PROFILE_NAME,
            'local',
            MediaDefinition::getEntityName(),
            0,
            250
        );
    }

    public function testSupports(): void
    {
        $supportsDefinition = $this->mediaConverter->supports(Shopware55Profile::PROFILE_NAME, MediaDefinition::getEntityName());

        static::assertTrue($supportsDefinition);
    }

    public function testConvert(): void
    {
        $mediaData = require __DIR__ . '/../../../_fixtures/media_data.php';

        $context = Context::createDefaultContext();
        $convertResult = $this->mediaConverter->convert($mediaData[0], $context, $this->migrationContext);

        $converted = $convertResult->getConverted();

        static::assertNull($convertResult->getUnmapped());
        static::assertArrayHasKey('id', $converted);
    }
}