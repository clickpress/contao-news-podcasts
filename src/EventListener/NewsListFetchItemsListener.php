<?php

/*
 * This file is part of NewsPodcasts.
 *
 * (c) Stefan Schulz-Lauterbach <ssl@clickpress.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clickpress\NewsPodcasts\EventListener;

use Clickpress\NewsPodcasts\Model\NewsPodcastsModel;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Model\Collection;
use Contao\Module;

/**
 * @Hook("newsListFetchItems")
 */
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
