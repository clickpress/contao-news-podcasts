<?php

/*
 * This file is part of NewsPodcasts.
 *
 * (c) Stefan Schulz-Lauterbach <ssl@clickpress.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clickpress\NewsPodcasts\Helper;

class PodcastFeedHelper extends \Feed
{
    /**
     * Generate podcast feed.
     *
     * @return string
     */
    public function generatePodcastFeed()
    {
        $this->adjustPublicationDate();

        $xml = '<?xml version="1.0" encoding="' . $GLOBALS['TL_CONFIG']['characterSet'] . '"?>';
        $xml .= '<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">';
        $xml .= '<channel>';
        $xml .= '<atom:link href="' . $this->podcastUrl . '" rel="self" type="application/rss+xml" />';
        $xml .= '<title>' . \StringUtil::specialchars($this->title) . '</title>';

        $xml .= '<copyright>&#xA9; ' . date('Y') . ' ' . $this->owner . '</copyright>';
        $xml .= '<itunes:author>' . $this->author . '</itunes:author>';

        $xml .= '<itunes:subtitle>' . \StringUtil::specialchars($this->subtitle) . '</itunes:subtitle>';
        $xml .= '<itunes:summary>' . \StringUtil::specialchars($this->description) . '</itunes:summary>';
        $xml .= '<description>' . \StringUtil::specialchars($this->description) . '</description>';

        $xml .= '<language>' . $this->language . '</language>';
        $xml .= '<itunes:explicit>' . ((!empty($this->explicit)) ? \StringUtil::specialchars($this->explicit) : 'no') . '</itunes:explicit>';
        $xml .= '<link>' . \StringUtil::specialchars($this->link) . '</link>';
        $xml .= '<lastBuildDate>' . date('r', $this->published) . '</lastBuildDate>';
        $xml .= '<generator>Contao Open Source CMS - News Podcasts</generator>';
        $xml .= '<itunes:owner>';
        $xml .=     '<itunes:name>' . $this->owner . '</itunes:name>';
        $xml .=     '<itunes:email>' . $this->email . '</itunes:email>';
        $xml .= '</itunes:owner>';

        $xml .= '<itunes:image href="' . $this->imageUrl . '" />';

        $xml .= $this->generateItunesCategory();

        foreach ($this->arrItems as $objItem) {
            $xml .= '<item>';
            $xml .= '<title>' . \StringUtil::specialchars(strip_tags($objItem->headline)) . '</title>';
            $xml .= '<author>' . \StringUtil::specialchars(strip_tags($objItem->author)) . '</author>';
            $xml .= '<itunes:subtitle><![CDATA[' . $objItem->subheadline . ']]></itunes:subtitle>';
            $xml .= '<description><![CDATA[' . $objItem->teaser . ']]></description>';
            $xml .= '<itunes:summary><![CDATA[' . $objItem->teaser . ']]></itunes:summary>';
            $xml .= '<link>' . \StringUtil::specialchars($objItem->link) . '</link>';
            $xml .= '<pubDate>' . date('r', $objItem->published) . '</pubDate>';
            $xml .= (!empty($objItem->explicit)) ? '<itunes:explicit>' . \StringUtil::specialchars($objItem->explicit) . '</itunes:explicit>' : '';
            $xml .= '<itunes:duration>' . $objItem->duration . '</itunes:duration>';

            // Add the GUID
            $xml .= '<guid isPermaLink="false">' . $objItem->guid . '</guid>';

            // Enclosures
            $xml .= '<enclosure url="' . $objItem->podcastUrl . '" length="' . $objItem->length . '" type="' . $objItem->type . '" />';

            $xml .= '</item>';
        }

        $xml .= '</channel>';
        $xml .= '</rss>';

        return $xml;
    }

    /**
     * Generate iTunes XML for categories.
     *
     * @return string
     */
    protected function generateItunesCategory()
    {
        $category = explode('|', $this->category);

        $catStr = '<itunes:category text="' . htmlentities($category[0]) . '">';
        if (isset($category[1])) {
            $catStr .= '<itunes:category text="' . htmlentities($category[1]) . '" />';
        }
        $catStr .= '</itunes:category>';

        return $catStr;
    }
}
