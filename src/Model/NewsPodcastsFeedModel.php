<?php

/*
 * This file is part of NewsPodcasts.
 *
 * (c) Stefan Schulz-Lauterbach <ssl@clickpress.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clickpress\NewsPodcasts\Model;

/**
 * Reads podcasts feeds.
 *
 * @property int    $id
 * @property int    $tstamp
 * @property string $title
 * @property string $alias
 * @property string $language
 * @property string $subtitle
 * @property string $summary
 * @property string $description
 * @property string $category
 * @property string $explicit
 * @property string $owner
 * @property string $email
 * @property string $author
 * @property string $image
 * @property string $archives
 * @property int    $maxItems
 * @property string $feedBase
 * @property bool   $addStatistics
 * @property string $statisticsPrefix
 * @property string $news_categoriesRoot
 *
 * @method static Collection|NewsPodcastsFeedModel[]|NewsPodcastsFeedModel|null findAll(array $opt = array())
 *
 * @author Stefan Schulz-Lauterbach
 */
class NewsPodcastsFeedModel extends \Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_news_podcasts_feed';

    /**
     * Find all feeds which include a certain news archive.
     *
     * @param int   $intId      The news archive ID
     * @param array $arrOptions An optional options array
     *
     * @return \Model\Collection|null A collection of models or null if the news archive is not part of a feed
     */
    public static function findByArchive($intId, array $arrOptions = [])
    {
        $t = static::$strTable;

        return static::findBy(["$t.archives LIKE '%\"" . (int) $intId . "\"%'"], null, $arrOptions);
    }

    /**
     * Find all feeds which include a certain news category, depends on news_categories module.
     *
     * @return \Model\Collection|null A collection of models or null if the news archive is not part of a feed
     */
    public static function findAllByNewsCategories()
    {
    }
}
