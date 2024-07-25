<?php

namespace Clickpress\NewsPodcasts\Backend;

use Clickpress\NewsPodcasts\Model\NewsPodcastsFeedModel;
use Contao\News;
use Contao\System;

/**
 * Class NewsPodcastsBackend.
 */
class NewsPodcastsBackend extends News
{


    /**
     * Contao hook removeOldFeeds.
     *
     * @return array
     */
    public static function preservePodcastFeeds(): array
    {
        $objFeeds = NewsPodcastsFeedModel::findAll();

        if (null === $objFeeds) {
            return [];
        }

        $arrFeeds = [];

        while ($objFeeds->next()) {
            $arrFeeds[] = $objFeeds->alias ?: 'news' . $objFeeds->id;
        }

        return $arrFeeds;
    }



    /**
     * Get all categories from yaml https://github.com/mr-rigden/Podcast-Categories-List.
     *
     * @return array
     */


    /**
     * Check if codefog/contao-news_categories is installed.
     *
     * @return bool
     */
    public static function checkNewsCategoriesBundle(): bool
    {
        $arrBundles = System::getContainer()->getParameter('kernel.bundles');

        return array_key_exists('CodefogNewsCategoriesBundle', $arrBundles);
    }
}
