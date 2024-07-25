<?php

namespace Clickpress\NewsPodcasts\EventListener;

use Clickpress\NewsPodcasts\Model\NewsPodcastsModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Model\Collection;
use Contao\Module;

#[AsHook('newsListFetchItems')]
class NewsListFetchItemsListener
{
    public function __invoke(array $newsArchives, ?bool $featuredOnly, int $limit, int $offset, Module $module): Collection|array|null
    {
        if ($newsArchives) {

            // Query the database and return the records
            return NewsPodcastsModel::findPublishedByPids($newsArchives);
        }

        return $newsArchives;
    }
}
