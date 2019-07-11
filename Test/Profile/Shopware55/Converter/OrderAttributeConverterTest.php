<?php declare(strict_types=1);

namespace SwagMigrationNext\Test\Profile\Shopware55\Converter;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagMigrationAssistant\Migration\Connection\SwagMigrationConnectionEntity;
use SwagMigrationAssistant\Migration\MigrationContext;
use SwagMigrationAssistant\Profile\Shopware\DataSelection\DataSet\OrderAttributeDataSet;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Local\ShopwareLocalGateway;
use SwagMigrationAssistant\Profile\Shopware55\Converter\Shopware55OrderAttributeConverter;
use SwagMigrationAssistant\Profile\Shopware55\Shopware55Profile;
use SwagMigrationAssistant\Test\Mock\Migration\Mapping\DummyMappingService;

class OrderAttributeConverterTest extends TestCase
{
    /**
     * @var MigrationContext
     */
    private $migrationContext;

    /**
     * @var Shopware55OrderAttributeConverter
     */
    private $converter;

    protected function setUp(): void
    {
        $this->converter = new Shopware55OrderAttributeConverter(new DummyMappingService());

        $runId = Uuid::randomHex();
        $connection = new SwagMigrationConnectionEntity();
        $connection->setProfileName(Shopware55Profile::PROFILE_NAME);
        $connection->setGatewayName(ShopwareLocalGateway::GATEWAY_NAME);
        $connection->setId(Uuid::randomHex());
        $connection->setName('ConntectionName');

        $this->migrationContext = new MigrationContext(
            new Shopware55Profile(),
            $connection,
            $runId,
            new OrderAttributeDataSet(),
            0,
            250
        );
    }

    public function testSupports(): void
    {
        $supportsDefinition = $this->converter->supports($this->migrationContext);

        static::assertTrue($supportsDefinition);
    }

    public function testConvertTextAttribute(): void
    {
        $categoryData = require __DIR__ . '/../../../_fixtures/attribute_data.php';

        $context = Context::createDefaultContext();
        $convertResult = $this->converter->convert($categoryData[3], $context, $this->migrationContext);

        $converted = $convertResult->getConverted();

        static::assertNull($convertResult->getUnmapped());
        static::assertArrayHasKey('id', $converted);
        static::assertArrayHasKey('relations', $converted);
        static::assertSame('order', $converted['relations'][0]['entityName']);
        static::assertSame('order_migration_ConntectionName_attr6', $converted['customFields'][0]['name']);
        static::assertSame('text', $converted['customFields'][0]['config']['type']);
        static::assertSame('text', $converted['customFields'][0]['config']['customFieldType']);
    }
}
