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
use Contao\Module;

/**
 * @Hook("newsListFetchItems")
 */
class NewsListFetchItemsListener
{
    public function __invoke(array $newsArchives, ?bool $featuredOnly, int $limit, int $offset, Module $module)
    {
        if ($newsArchives) {
            dump($newsArchives);

            // Query the database and return the records
            $news = NewsPodcastsModel::findPublishedByPids($newsArchives);

            return $news;
        }

        return $newsArchives;
    }
}
