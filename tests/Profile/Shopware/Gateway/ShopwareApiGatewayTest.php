<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Test\Profile\Shopware\Gateway;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use SwagMigrationAssistant\Exception\MigrationException;
use SwagMigrationAssistant\Migration\Connection\SwagMigrationConnectionEntity;
use SwagMigrationAssistant\Migration\EnvironmentInformation;
use SwagMigrationAssistant\Migration\Gateway\Reader\ReaderRegistry;
use SwagMigrationAssistant\Migration\MigrationContext;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Api\Reader\EnvironmentReader;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Api\Reader\ProductReader;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Api\Reader\TableCountReader;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Api\Reader\TableReader;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Api\ShopwareApiGateway;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Connection\ConnectionFactory;
use SwagMigrationAssistant\Profile\Shopware55\Shopware55Profile;
use SwagMigrationAssistant\Test\Mock\Gateway\Dummy\Api\Reader\EnvironmentDummyReader;
use SwagMigrationAssistant\Test\Mock\Gateway\Dummy\Api\Reader\TableCountDummyReader;
use SwagMigrationAssistant\Test\Mock\Migration\Logging\DummyLoggingService;
use SwagMigrationAssistant\Test\Profile\Shopware\DataSet\FooDataSet;

#[Package('services-settings')]
class ShopwareApiGatewayTest extends TestCase
{
    use KernelTestBehaviour;

    public function testReadFailed(): void
    {
        $migrationContext = new MigrationContext(
            new Shopware55Profile(),
            new SwagMigrationConnectionEntity(),
            '',
            new FooDataSet()
        );

        $connectionFactory = new ConnectionFactory();
        $apiReader = new ProductReader($connectionFactory);
        $environmentReader = new EnvironmentReader($connectionFactory);
        $tableReader = new TableReader($connectionFactory);
        $tableCountReader = new TableCountReader($connectionFactory, new DummyLoggingService());

        $gateway = new ShopwareApiGateway(
            new ReaderRegistry([$apiReader]),
            $environmentReader,
            $tableReader,
            $tableCountReader,
            $this->getContainer()->get('currency.repository'),
            $this->getContainer()->get('language.repository')
        );
        $migrationContext->setGateway($gateway);

        try {
            $gateway->read($migrationContext);
        } catch (MigrationException $e) {
            static::assertSame(MigrationException::READER_NOT_FOUND, $e->getErrorCode());

            return;
        }

        static::fail('Expected exception not thrown');
    }

    public function testReadEnvironmentInformationFailed(): void
    {
        $connection = new SwagMigrationConnectionEntity();
        $connection->setCredentialFields([
            'endpoint' => 'testing',
            'apiUser' => 'testing',
            'apiKey' => 'testing',
        ]);
        $migrationContext = new MigrationContext(
            new Shopware55Profile(),
            $connection
        );

        $connectionFactory = new ConnectionFactory();
        $apiReader = new ProductReader($connectionFactory);
        $environmentReader = new EnvironmentReader($connectionFactory);
        $tableReader = new TableReader($connectionFactory);
        $tableCountReader = new TableCountReader($connectionFactory, new DummyLoggingService());

        $gateway = new ShopwareApiGateway(
            new ReaderRegistry([$apiReader]),
            $environmentReader,
            $tableReader,
            $tableCountReader,
            $this->getContainer()->get('currency.repository'),
            $this->getContainer()->get('language.repository')
        );
        $response = $gateway->readEnvironmentInformation($migrationContext, Context::createDefaultContext());
        $errorException = MigrationException::gatewayRead('Shopware 5.5 Api SwagMigrationEnvironment');

        static::assertSame($response->getTotals(), []);
        static::assertNotNull($response->getRequestStatus());
        static::assertSame($response->getRequestStatus()->getCode(), $errorException->getErrorCode());
        static::assertSame($response->getRequestStatus()->getMessage(), $errorException->getMessage());
        static::assertFalse($response->getRequestStatus()->getIsWarning());
    }

    public function testReadEnvironmentInformation(): void
    {
        $connection = new SwagMigrationConnectionEntity();
        $connection->setCredentialFields(['endpoint' => 'foo']);

        $migrationContext = new MigrationContext(
            new Shopware55Profile(),
            $connection
        );

        $connectionFactory = new ConnectionFactory();
        $apiReader = new ProductReader($connectionFactory);
        $environmentReader = new EnvironmentDummyReader($connectionFactory);
        $tableReader = new TableReader($connectionFactory);
        $tableCountReader = new TableCountDummyReader($connectionFactory, new DummyLoggingService());

        $gateway = new ShopwareApiGateway(
            new ReaderRegistry([$apiReader]),
            $environmentReader,
            $tableReader,
            $tableCountReader,
            $this->getContainer()->get('currency.repository'),
            $this->getContainer()->get('language.repository')
        );
        $response = $gateway->readEnvironmentInformation($migrationContext, Context::createDefaultContext());

        static::assertSame('Shopware', $response->getSourceSystemName());
        static::assertSame('___VERSION___', $response->getSourceSystemVersion());
        static::assertSame('foo', $response->getSourceSystemDomain());
    }

    public function testReadEnvironmentInformationWithoutSourceDefaultLanguage(): void
    {
        $connection = new SwagMigrationConnectionEntity();
        $connection->setCredentialFields(['endpoint' => 'foo']);

        $migrationContext = new MigrationContext(
            new Shopware55Profile(),
            $connection
        );

        $connectionFactory = new ConnectionFactory();
        $apiReader = new ProductReader($connectionFactory);
        $environmentReader = new EnvironmentDummyReader($connectionFactory);
        $environmentReader->setDummyData(['defaultShopLanguage' => null]);
        $tableReader = new TableReader($connectionFactory);
        $tableCountReader = new TableCountDummyReader($connectionFactory, new DummyLoggingService());

        $gateway = new ShopwareApiGateway(
            new ReaderRegistry([$apiReader]),
            $environmentReader,
            $tableReader,
            $tableCountReader,
            $this->getContainer()->get('currency.repository'),
            $this->getContainer()->get('language.repository')
        );
        /** @var EnvironmentInformation $response */
        $response = $gateway->readEnvironmentInformation($migrationContext, Context::createDefaultContext());
        static::assertInstanceOf(EnvironmentInformation::class, $response);

        static::assertSame('Shopware', $response->getSourceSystemName());
        static::assertSame('___VERSION___', $response->getSourceSystemVersion());
        static::assertSame('foo', $response->getSourceSystemDomain());
        static::assertSame('en-GB', $response->getSourceSystemLocale());
    }
}
