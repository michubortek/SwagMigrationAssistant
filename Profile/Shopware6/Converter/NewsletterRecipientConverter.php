<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Profile\Shopware6\Converter;

use Shopware\Core\Framework\Log\Package;
use SwagMigrationAssistant\Migration\Converter\ConvertStruct;
use SwagMigrationAssistant\Migration\DataSelection\DefaultEntities;
use SwagMigrationAssistant\Migration\MigrationContextInterface;
use SwagMigrationAssistant\Profile\Shopware6\DataSelection\DataSet\NewsletterRecipientDataSet;
use SwagMigrationAssistant\Profile\Shopware6\Shopware6MajorProfile;

#[Package('services-settings')]
class NewsletterRecipientConverter extends ShopwareConverter
{
    public function supports(MigrationContextInterface $migrationContext): bool
    {
        return $migrationContext->getProfile()->getName() === Shopware6MajorProfile::PROFILE_NAME
            && $this->getDataSetEntity($migrationContext) === NewsletterRecipientDataSet::getEntity();
    }

    protected function convertData(array $data): ConvertStruct
    {
        $converted = $data;

        $this->mainMapping = $this->getOrCreateMappingMainCompleteFacade(
            DefaultEntities::NEWSLETTER_RECIPIENT,
            $data['id'],
            $converted['id']
        );

        $converted['languageId'] = $this->getMappingIdFacade(DefaultEntities::LANGUAGE, $data['languageId']);
        $converted['salesChannelId'] = $this->getMappingIdFacade(DefaultEntities::SALES_CHANNEL, $data['salesChannelId']);
        $converted['salutationId'] = $this->getMappingIdFacade(DefaultEntities::SALUTATION, $data['salutationId']);

        return new ConvertStruct($converted, null, $this->mainMapping['id']);
    }
}
