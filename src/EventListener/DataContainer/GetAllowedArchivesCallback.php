<?php
namespace Clickpress\NewsPodcasts\EventListener\DataContainer;

use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\NewsArchiveModel;

#[AsCallback(table: 'tl_news_podcasts_feed', target: 'fields.archives.options')]
class GetAllowedArchivesCallback
{
    public function __invoke(): array
    {
        $user = BackendUser::getInstance();
        if (BackendUser::getInstance()->isAdmin) {
            $objArchive = NewsArchiveModel::findAll();
        } else {
            $objArchive = NewsArchiveModel::findMultipleByIds($user->news);
        }

        $return = [];

        if (null !== $objArchive) {
            while ($objArchive->next()) {
                $return[$objArchive->id] = $objArchive->title;
            }
        }

        return $return;
    }
}
