<?php declare(strict_types=1);

namespace SwagMigrationAssistant\Profile\Shopware\Gateway\Local\Reader;

use SwagMigrationAssistant\Migration\DataSelection\DefaultEntities;
use SwagMigrationAssistant\Migration\MigrationContextInterface;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Local\ShopwareLocalGateway;
use SwagMigrationAssistant\Profile\Shopware\ShopwareProfileInterface;

class MediaAlbumReader extends AbstractReader
{
    public function supports(MigrationContextInterface $migrationContext): bool
    {
        return $migrationContext->getProfile() instanceof ShopwareProfileInterface
            && $migrationContext->getGateway()->getName() === ShopwareLocalGateway::GATEWAY_NAME
            && $migrationContext->getDataSet()::getEntity() === DefaultEntities::MEDIA_FOLDER;
    }

    public function read(MigrationContextInterface $migrationContext): array
    {
        $this->setConnection($migrationContext);
        $fetchedAlbums = $this->fetchAlbums();

        $albums = $this->mapData(
            $fetchedAlbums,
            [],
            ['album']
        );

        $albums = $this->prepareMediaAlbums($albums);

        return $this->cleanupResultSet($albums);
    }

    private function fetchAlbums(): array
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_media_album', 'album');
        $this->addTableSelection($query, 's_media_album', 'album');

        $query->leftJoin('album', 's_media_album_settings', 'setting', 'setting.albumID = album.id');
        $this->addTableSelection($query, 's_media_album_settings', 'setting');

        $query->orderBy('parentID');

        return $query->execute()->fetchAll();
    }

    private function prepareMediaAlbums(array $mediaAlbums): array
    {
        // represents the main language of the migrated shop
        $locale = $this->getDefaultShopLocale();

        $albums = [];
        foreach ($mediaAlbums as $key => $mediaAlbum) {
            if ($mediaAlbum['parentID'] !== null) {
                continue;
            }

            $mediaAlbum['_locale'] = $locale;
            $albums[] = [$mediaAlbum];
            unset($mediaAlbums[$key]);

            $childAlbums = $this->getChildAlbums($mediaAlbums, $mediaAlbum['id'], $locale);

            if (!empty($childAlbums)) {
                $albums[] = $childAlbums;
            }
        }
        unset($mediaAlbum);

        if (empty($albums)) {
            return $albums;
        }

        return array_merge(...$albums);
    }

    private function getChildAlbums(array &$mediaAlbums, $id, $locale): array
    {
        $albums = [];
        foreach ($mediaAlbums as $key => $mediaAlbum) {
            if ($mediaAlbum['parentID'] !== $id) {
                continue;
            }

            $mediaAlbum['_locale'] = $locale;
            $albums[] = [$mediaAlbum];
            unset($mediaAlbums[$key]);

            $childAlbums = $this->getChildAlbums($mediaAlbums, $mediaAlbum['id'], $locale);

            if (!empty($childAlbums)) {
                $albums[] = $childAlbums;
            }
        }

        if (empty($albums)) {
            return $albums;
        }

        return array_merge(...$albums);
    }
}
