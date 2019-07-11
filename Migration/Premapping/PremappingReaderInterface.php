<?php declare(strict_types=1);

namespace SwagMigrationAssistant\Migration\Premapping;

use Shopware\Core\Framework\Context;
use SwagMigrationAssistant\Migration\MigrationContextInterface;

interface PremappingReaderInterface
{
    public static function getMappingName(): string;

    /**
     * @param string[] $entityGroupNames
     */
    public function supports(MigrationContextInterface $migrationContext, array $entityGroupNames): bool;

    public function getPremapping(Context $context, MigrationContextInterface $migrationContext): PremappingStruct;
}
