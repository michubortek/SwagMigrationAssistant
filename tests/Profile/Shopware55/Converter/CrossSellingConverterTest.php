<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Test\Profile\Shopware55\Converter;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagMigrationAssistant\Migration\Connection\SwagMigrationConnectionEntity;
use SwagMigrationAssistant\Migration\Converter\ConvertStruct;
use SwagMigrationAssistant\Migration\DataSelection\DefaultEntities;
use SwagMigrationAssistant\Migration\Mapping\MappingService;
use SwagMigrationAssistant\Migration\MigrationContext;
use SwagMigrationAssistant\Migration\MigrationContextInterface;
use SwagMigrationAssistant\Profile\Shopware\DataSelection\DataSet\CrossSellingDataSet;
use SwagMigrationAssistant\Profile\Shopware55\Converter\Shopware55CrossSellingConverter;
use SwagMigrationAssistant\Profile\Shopware55\Shopware55Profile;
use SwagMigrationAssistant\Test\Mock\Migration\Logging\DummyLoggingService;
use SwagMigrationAssistant\Test\Mock\Migration\Mapping\DummyMappingService;

#[Package('services-settings')]
class CrossSellingConverterTest extends TestCase
{
    private Shopware55CrossSellingConverter $crossSellingConverter;

    private DummyLoggingService $loggingService;

    private SwagMigrationConnectionEntity $connection;

    private MigrationContextInterface $migrationContext;

    private DummyMappingService $mappingService;

    /**
     * @var list<array<string, mixed>>
     */
    private array $products = [];

    /**
     * @var list<string>
     */
    private array $type = [
        'Similar Items',
        'Accessory Items',
    ];

    /**
     * @var array<string, mixed>
     */
    private array $compareProduct = [
        'id' => null,
        'name' => 'Similar Items',
        'type' => 'productList',
        'active' => true,
        'productId' => null,
        'assignedProducts' => [
            0 => [
                'id' => null,
                'position' => '1',
                'productId' => null,
            ],
        ],
    ];

    protected function setUp(): void
    {
        $this->mappingService = new DummyMappingService();
        $this->loggingService = new DummyLoggingService();
        $this->crossSellingConverter = new Shopware55CrossSellingConverter($this->mappingService, $this->loggingService);

        $runId = Uuid::randomHex();
        $this->connection = new SwagMigrationConnectionEntity();
        $this->connection->setId(Uuid::randomHex());
        $this->connection->setProfileName(Shopware55Profile::PROFILE_NAME);
        $this->connection->setName('shopware');

        $this->migrationContext = new MigrationContext(
            new Shopware55Profile(),
            $this->connection,
            $runId,
            new CrossSellingDataSet(),
            0,
            250
        );

        $this->createMapping();
    }

    public function testSupports(): void
    {
        $supportsDefinition = $this->crossSellingConverter->supports($this->migrationContext);

        static::assertTrue($supportsDefinition);
    }

    public function testConvert(): void
    {
        $data = require __DIR__ . '/../../../_fixtures/cross_selling_data.php';

        $context = Context::createDefaultContext();
        $convertResult = $this->crossSellingConverter->convert($data[0], $context, $this->migrationContext);
        $this->checkProduct(0, 1, $convertResult, $this->type[0], '1');
    }

    public function testConvertMultipleItems(): void
    {
        $data = require __DIR__ . '/../../../_fixtures/cross_selling_data.php';

        $context = Context::createDefaultContext();
        $convertResult = $this->crossSellingConverter->convert($data[1], $context, $this->migrationContext);
        $this->checkProduct(1, 2, $convertResult, $this->type[1], '2');

        $convertResult = $this->crossSellingConverter->convert($data[2], $context, $this->migrationContext);
        $this->checkProduct(1, 3, $convertResult, $this->type[1], '3');

        $convertResult = $this->crossSellingConverter->convert($data[3], $context, $this->migrationContext);
        $this->checkProduct(1, 0, $convertResult, $this->type[0], '4');
    }

    public function testConvertMultipleTime(): void
    {
        $data = require __DIR__ . '/../../../_fixtures/cross_selling_data.php';

        $context = Context::createDefaultContext();
        $convertResult1 = $this->crossSellingConverter->convert($data[1], $context, $this->migrationContext);
        $converted1 = $convertResult1->getConverted();
        static::assertNotNull($converted1);

        $convertResult2 = $this->crossSellingConverter->convert($data[1], $context, $this->migrationContext);
        $converted2 = $convertResult2->getConverted();
        static::assertNotNull($converted2);

        static::assertSame($converted1['id'], $converted2['id']);
        static::assertSame($converted1['productId'], $converted2['productId']);
        static::assertSame($converted1['assignedProducts'][0]['id'], $converted2['assignedProducts']['0']['id']);
        static::assertSame($converted1['assignedProducts'][0]['position'], $converted2['assignedProducts']['0']['position']);
        static::assertSame($converted1['assignedProducts'][0]['productId'], $converted2['assignedProducts']['0']['productId']);
    }

    public function testConvertWithoutMapping(): void
    {
        $data = require __DIR__ . '/../../../_fixtures/cross_selling_data.php';
        $product = $data[0];
        $product['articleID'] = '99';

        $context = Context::createDefaultContext();
        $convertResult = $this->crossSellingConverter->convert($product, $context, $this->migrationContext);

        static::assertNotNull($convertResult->getUnmapped());
        static::assertNull($convertResult->getConverted());

        $logs = $this->loggingService->getLoggingArray();
        static::assertCount(1, $logs);
        static::assertSame('SWAG_MIGRATION__SHOPWARE_ASSOCIATION_REQUIRED_MISSING_PRODUCT', $logs[0]['code']);
        static::assertSame('99', $logs[0]['parameters']['sourceId']);

        $this->loggingService->resetLogging();
        $data[0]['relatedarticle'] = '80';
        $convertResult = $this->crossSellingConverter->convert($data[0], $context, $this->migrationContext);

        static::assertNotNull($convertResult->getUnmapped());
        static::assertNull($convertResult->getConverted());

        $logs = $this->loggingService->getLoggingArray();
        static::assertCount(1, $logs);
        static::assertSame('SWAG_MIGRATION__SHOPWARE_ASSOCIATION_REQUIRED_MISSING_PRODUCT', $logs[0]['code']);
        static::assertSame('80', $logs[0]['parameters']['sourceId']);
    }

    public function testConvertCreatesAdditionalMappingForTypeAndArticleId(): void
    {
        $crossSellingData = require __DIR__ . '/../../../_fixtures/cross_selling_data.php';

        $context = Context::createDefaultContext();
        $checksum = \md5(\serialize($crossSellingData[1]));
        $connectionId = $this->connection->getId();

        $mappingServiceMock = $this->createMock(MappingService::class);
        $mappingServiceMock->method('getMapping')->willReturn($this->products[0]);
        $mappingServiceMock->expects(static::once())->method('updateMapping');
        $mappingServiceMock->expects(static::exactly(3))
            ->method('getOrCreateMapping')
            ->willReturnMap([
                [
                    $connectionId,
                    DefaultEntities::CROSS_SELLING,
                    '89_product_cross_selling_accessory',
                    $context,
                    $checksum,
                    null,
                    null,
                    null,
                    \array_merge($this->products[0], ['oldIdentifier' => '117']),
                ], [
                    $connectionId,
                    'product_cross_selling_accessory',
                    '117',
                    $context,
                    $checksum,
                    null,
                    null,
                    null,
                    $this->products[0],
                ], [
                    $connectionId,
                    'product_cross_selling_accessory_relation',
                    '117_114',
                    $context,
                    null,
                    null,
                    null,
                    null,
                    $this->products[0],
                ],
            ]);

        $crossSellingConverter = new Shopware55CrossSellingConverter($mappingServiceMock, $this->loggingService);
        $result = $crossSellingConverter->convert($crossSellingData[1], $context, $this->migrationContext);

        $converted = $result->getConverted();
        static::assertNotEmpty($converted);
        static::assertArrayHasKey('assignedProducts', $converted);
        static::assertNotEmpty($converted['assignedProducts']);
    }

    private function createMapping(): void
    {
        foreach (['123', '117', '114', '113'] as $identifier) {
            $this->products[] = $this->mappingService->getOrCreateMapping(
                $this->connection->getId(),
                DefaultEntities::PRODUCT_CONTAINER,
                $identifier,
                Context::createDefaultContext(),
                null,
                [],
                Uuid::randomHex()
            );
        }
    }

    private function checkProduct(int $fromIndex, int $toIndex, ConvertStruct $convertStruct, string $type, string $position): void
    {
        $converted = $convertStruct->getConverted();
        static::assertNotNull($converted);
        $this->compareProduct['id'] = $converted['id'];
        $this->compareProduct['name'] = $type;
        $this->compareProduct['productId'] = $this->products[$fromIndex]['entityUuid'];
        $this->compareProduct['assignedProducts']['0']['id'] = $converted['assignedProducts']['0']['id'];
        $this->compareProduct['assignedProducts']['0']['productId'] = $this->products[$toIndex]['entityUuid'];
        $this->compareProduct['assignedProducts']['0']['position'] = $position;

        static::assertNull($convertStruct->getUnmapped());
        static::assertNotNull($convertStruct->getMappingUuid());
        static::assertSame($this->compareProduct, $converted);
    }
}
