<?php declare(strict_types=1);

namespace SwagMigrationAssistant\Profile\Shopware54;

use SwagMigrationAssistant\Profile\Shopware\ShopwareProfileInterface;

class Shopware54Profile implements ShopwareProfileInterface
{
    public const PROFILE_NAME = 'shopware54';

    public const SOURCE_SYSTEM_NAME = 'Shopware';

    public const SOURCE_SYSTEM_VERSION = '5.4';

    public function getName(): string
    {
        return self::PROFILE_NAME;
    }

    public function getSourceSystemName(): string
    {
        return self::SOURCE_SYSTEM_NAME;
    }

    public function getVersion(): string
    {
        return self::SOURCE_SYSTEM_VERSION;
    }
}
