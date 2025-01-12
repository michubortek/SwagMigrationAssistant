<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Test\Migration\Controller;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityWriter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagMigrationAssistant\Controller\PremappingController;
use SwagMigrationAssistant\Migration\Gateway\GatewayRegistry;
use SwagMigrationAssistant\Migration\Mapping\MappingService;
use SwagMigrationAssistant\Migration\Mapping\SwagMigrationMappingCollection;
use SwagMigrationAssistant\Migration\Mapping\SwagMigrationMappingDefinition;
use SwagMigrationAssistant\Migration\MigrationContext;
use SwagMigrationAssistant\Migration\MigrationContextFactory;
use SwagMigrationAssistant\Migration\Premapping\PremappingEntityStruct;
use SwagMigrationAssistant\Migration\Premapping\PremappingReaderRegistry;
use SwagMigrationAssistant\Migration\Premapping\PremappingStruct;
use SwagMigrationAssistant\Migration\Run\MigrationStep;
use SwagMigrationAssistant\Migration\Run\SwagMigrationRunCollection;
use SwagMigrationAssistant\Migration\Service\PremappingService;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Local\ShopwareLocalGateway;
use SwagMigrationAssistant\Profile\Shopware\Premapping\OrderStateReader;
use SwagMigrationAssistant\Profile\Shopware\Premapping\TransactionStateReader;
use SwagMigrationAssistant\Profile\Shopware55\Shopware55Profile;
use SwagMigrationAssistant\Test\MigrationServicesTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Package('services-settings')]
class PremappingControllerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use MigrationServicesTrait;

    /**
     * @var EntityRepository<SwagMigrationRunCollection>
     */
    private EntityRepository $runRepo;

    /**
     * @var EntityRepository<SwagMigrationMappingCollection>
     */
    private EntityRepository $mappingRepo;

    private PremappingController $controller;

    private PremappingStruct $premapping;

    private MappingService $mappingService;

    private string $runUuid;

    private Context $context;

    private string $connectionId = '';

    private PremappingEntityStruct $firstState;

    private PremappingEntityStruct $secondState;

    protected function setUp(): void
    {
        $this->context = Context::createDefaultContext();
        $connectionRepo = $this->getContainer()->get('swag_migration_connection.repository');
        $this->runRepo = $this->getContainer()->get('swag_migration_run.repository');
        $stateMachineRepo = $this->getContainer()->get('state_machine.repository');
        $stateMachineStateRepo = $this->getContainer()->get('state_machine_state.repository');
        $this->mappingRepo = $this->getContainer()->get('swag_migration_mapping.repository');
        $migrationContextFactory = $this->getContainer()->get(MigrationContextFactory::class);

        $gatewayRegistry = $this->getContainer()->get(GatewayRegistry::class);
        $this->createMappingService();

        $this->controller = new PremappingController(
            new PremappingService(
                new PremappingReaderRegistry(
                    [
                        new OrderStateReader($stateMachineRepo, $stateMachineStateRepo, $gatewayRegistry),
                        new TransactionStateReader($stateMachineRepo, $stateMachineStateRepo, $gatewayRegistry),
                    ]
                ),
                $this->mappingService,
                $this->mappingRepo,
                $connectionRepo
            ),
            $migrationContextFactory
        );

        $this->context->scope(MigrationContext::SOURCE_CONTEXT, function (Context $context) use ($connectionRepo): void {
            $this->connectionId = Uuid::randomHex();
            $connectionRepo->create(
                [
                    [
                        'id' => $this->connectionId,
                        'name' => 'myConnection',
                        'credentialFields' => [
                            'endpoint' => 'testEndpoint',
                            'apiUser' => 'testUser',
                            'apiKey' => 'testKey',
                        ],
                        'profileName' => Shopware55Profile::PROFILE_NAME,
                        'gatewayName' => ShopwareLocalGateway::GATEWAY_NAME,
                    ],
                ],
                $context
            );
        });

        $generalSettingRepo = $this->getContainer()->get('swag_migration_general_setting.repository');
        $setting = $generalSettingRepo->searchIds(new Criteria(), $this->context)->firstId();

        $generalSettingRepo->update([
            [
                'id' => $setting,
                'selectedConnectionId' => $this->connectionId,
            ],
        ], $this->context);

        $this->runUuid = Uuid::randomHex();
        $this->runRepo->create(
            [
                [
                    'id' => $this->runUuid,
                    'connectionId' => $this->connectionId,
                    'step' => MigrationStep::FETCHING->value,
                ],
            ],
            $this->context
        );

        $firstStateUuid = Uuid::randomHex();
        $this->firstState = new PremappingEntityStruct('0', 'First State', $firstStateUuid);

        $secondStateUuid = Uuid::randomHex();
        $this->secondState = new PremappingEntityStruct('1', 'Second State', $secondStateUuid);

        $this->premapping = new PremappingStruct(OrderStateReader::getMappingName(), [$this->firstState, $this->secondState]);
    }

    public function testGeneratePremappingWithoutDataSelectionIds(): void
    {
        try {
            $this->controller->generatePremapping(new Request(), $this->context);
        } catch (RoutingException $e) {
            static::assertSame(Response::HTTP_BAD_REQUEST, $e->getStatusCode());
            static::assertSame(RoutingException::MISSING_REQUEST_PARAMETER_CODE, $e->getErrorCode());
            static::assertArrayHasKey('parameterName', $e->getParameters());
            static::assertSame($e->getParameters()['parameterName'], 'dataSelectionIds');
        }
    }

    public function testWritePremapping(): void
    {
        $request = new Request([], [
            'premapping' => \json_decode((string) (new JsonResponse([$this->premapping]))->getContent(), true),
        ]);

        $this->controller->writePremapping(
            $request,
            $this->context
        );

        $firstMapping = $this->mappingService->getMapping(
            $this->connectionId,
            OrderStateReader::getMappingName(),
            '0',
            $this->context
        );

        $secondMapping = $this->mappingService->getMapping(
            $this->connectionId,
            OrderStateReader::getMappingName(),
            '1',
            $this->context
        );

        static::assertNotNull($firstMapping);
        static::assertNotNull($secondMapping);
        static::assertSame($this->firstState->getDestinationUuid(), $firstMapping['entityUuid']);
        static::assertSame($this->secondState->getDestinationUuid(), $secondMapping['entityUuid']);
    }

    public function testWritePremappingWithoutPremapping(): void
    {
        try {
            $this->controller->writePremapping(new Request(), $this->context);
        } catch (RoutingException $e) {
            static::assertSame(Response::HTTP_BAD_REQUEST, $e->getStatusCode());
            static::assertSame(RoutingException::MISSING_REQUEST_PARAMETER_CODE, $e->getErrorCode());
            static::assertArrayHasKey('parameterName', $e->getParameters());
            static::assertSame($e->getParameters()['parameterName'], 'premapping');
        }
    }

    public function testWritePremappingTwice(): void
    {
        $request = new Request([], [
            'runUuid' => $this->runUuid,
            'premapping' => \json_decode((string) (new JsonResponse([$this->premapping]))->getContent(), true),
        ]);

        $this->controller->writePremapping(
            $request,
            $this->context
        );

        $firstMapping = $this->mappingService->getMapping(
            $this->connectionId,
            OrderStateReader::getMappingName(),
            '0',
            $this->context
        );

        $secondMapping = $this->mappingService->getMapping(
            $this->connectionId,
            OrderStateReader::getMappingName(),
            '1',
            $this->context
        );

        static::assertNotNull($firstMapping);
        static::assertNotNull($secondMapping);
        static::assertSame($this->firstState->getDestinationUuid(), $firstMapping['entityUuid']);
        static::assertSame($this->secondState->getDestinationUuid(), $secondMapping['entityUuid']);

        $firstStateUuid = Uuid::randomHex();
        $firstState = new PremappingEntityStruct('0', 'First State', $firstStateUuid);

        $secondStateUuid = Uuid::randomHex();
        $secondState = new PremappingEntityStruct('1', 'Second State', $secondStateUuid);

        $premapping = new PremappingStruct(OrderStateReader::getMappingName(), [$firstState, $secondState]);

        $request = new Request([], [
            'runUuid' => $this->runUuid,
            'premapping' => \json_decode((string) (new JsonResponse([$premapping]))->getContent(), true),
        ]);

        // reset mapping and DB cache
        $this->clearCacheData();
        $this->createMappingService();

        $this->controller->writePremapping(
            $request,
            $this->context
        );

        $firstMapping = $this->mappingService->getMapping(
            $this->connectionId,
            OrderStateReader::getMappingName(),
            '0',
            $this->context
        );

        $secondMapping = $this->mappingService->getMapping(
            $this->connectionId,
            OrderStateReader::getMappingName(),
            '1',
            $this->context
        );

        static::assertNotNull($firstMapping);
        static::assertNotNull($secondMapping);
        static::assertSame($firstState->getDestinationUuid(), $firstMapping['entityUuid']);
        static::assertSame($secondState->getDestinationUuid(), $secondMapping['entityUuid']);
    }

    private function createMappingService(): void
    {
        $this->mappingService = new MappingService(
            $this->mappingRepo,
            $this->getContainer()->get('locale.repository'),
            $this->getContainer()->get('language.repository'),
            $this->getContainer()->get('country.repository'),
            $this->getContainer()->get('currency.repository'),
            $this->getContainer()->get('tax.repository'),
            $this->getContainer()->get('number_range.repository'),
            $this->getContainer()->get('rule.repository'),
            $this->getContainer()->get('media_thumbnail_size.repository'),
            $this->getContainer()->get('media_default_folder.repository'),
            $this->getContainer()->get('category.repository'),
            $this->getContainer()->get('cms_page.repository'),
            $this->getContainer()->get('delivery_time.repository'),
            $this->getContainer()->get('document_type.repository'),
            $this->getContainer()->get(EntityWriter::class),
            $this->getContainer()->get(SwagMigrationMappingDefinition::class),
            new NullLogger()
        );
    }
}
