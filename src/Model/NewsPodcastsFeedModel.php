<?php

/*
 * This file is part of NewsPodcasts.
 *
 * (c) Stefan Schulz-Lauterbach <ssl@clickpress.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Run in a custom namespace, so the class can be replaced.
 */

namespace Clickpress\NewsPodcasts\Model;

/**
 * Reads podcasts feeds.
 *
 * @author    Leo Feyer <https://github.com/leofeyer>
 * @copyright Leo Feyer 2005-2014
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
}
