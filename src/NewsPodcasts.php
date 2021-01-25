<?php

declare(strict_types=1);

/*
 * This file is part of NewsPodcasts.
 *
 * (c) Stefan Schulz-Lauterbach <ssl@clickpress.de>
 *
 * @license LGPL-3.0-or-later
 */

namespace Clickpress\NewsPodcasts;

use Clickpress\NewsPodcasts\Helper\GetMp3Duration;
use Clickpress\NewsPodcasts\Helper\iTunesFeed;
use Clickpress\NewsPodcasts\Model\NewsPodcastsFeedModel;
use Clickpress\NewsPodcasts\Model\NewsPodcastsModel;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Environment;
use Contao\File;
use Contao\FilesModel;
use Contao\Frontend;
use Contao\Input;
use Contao\PageModel;
use Contao\System;
use Exception;
use Psr\Log\LogLevel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class NewsPodcasts.
 *
 * @copyright  CLICKPRESS Internetagentur 2015
 * @author     Stefan Schulz-Lauterbach
 */
class NewsPodcasts extends Frontend
{
    /**
     * Update a particular RSS feed.
     *
     * @param $intId
     *
     * @throws Exception
     */
    public function generateFeed($intId)
    {
        $objFeed = NewsPodcastsFeedModel::findByArchive($intId);

        if (null === $objFeed) {
            return;
        }

        $objFeed->feedName = $objFeed->alias ?: 'itunes' . $objFeed->id;

        // Delete XML file
        if ('delete' === Input::get('act')) {
            $this->import('Files');
            $this->Files->delete($objFeed->feedName . '.xml');
        } // Update XML file
        else {
            $this->generateFiles($objFeed->row());
            $logger = System::getContainer()->get('monolog.logger.contao');
            $logger->log(LogLevel::INFO, 'Generated podcast feed "' . $objFeed->feedName . '.xml"', ['contao' => new ContaoContext(__METHOD__, TL_CRON)]);
        }
    }

    /**
     * Delete old files and generate all feeds.
     */
    public function generateFeeds()
    {
        $logger = \System::getContainer()->get('monolog.logger.contao');
        $logger->log(LogLevel::INFO, 'TEST', ['contao' => new ContaoContext(__METHOD__, ContaoContext::CRON)]);
        $objFeed = NewsPodcastsFeedModel::findAll();
#dump($objFeed);die();
        if (null !== $objFeed) {
            while ($objFeed->next()) {
                $objFeed->feedName = $objFeed->alias ?: 'itunes_' . $objFeed->id;
                self::generateFiles($objFeed->row());
                $logger = \System::getContainer()->get('monolog.logger.contao');
                $logger->log(LogLevel::INFO, 'Generated podcast feed "' . $objFeed->feedName . '.xml"', ['contao' => new ContaoContext(__METHOD__, ContaoContext::CRON)]);
            }
        }
    }

    /**
     * Generate all feeds including a certain archive.
     *
     * @param $intId
     *
     * @throws Exception
     */
    public function generateFeedsByArchive($intId)
    {
        $objFeed = NewsPodcastsFeedModel::findByArchive($intId);

        if (null !== $objFeed) {
            while ($objFeed->next()) {
                $objFeed->feedName = $objFeed->alias ?: 'itunes' . $objFeed->id;

                // Update the XML file
                $this->generateFiles($objFeed->row());
                $logger = \System::getContainer()->get('monolog.logger.contao');
                $logger->log(LogLevel::INFO, 'Generated podcast feed "' . $objFeed->feedName . '.xml"', ['contao' => new ContaoContext(__METHOD__, TL_CRON)]);
            }
        }
    }

    /**
     * @param $arrFeed
     *
     * @throws Exception
     */
    protected function generateFiles($arrFeed)
    {
        $arrArchives = \StringUtil::deserialize($arrFeed['archives']);

        if (!\is_array($arrArchives) || empty($arrArchives)) {
            return;
        }

        $strType = 'generateItunes';

        $strLink = $arrFeed['feedBase'] ?: Environment::get('base');
        $strFile = $arrFeed['feedName'];

        $objFeed = new iTunesFeed($strFile);
        $objFeed->link = $strLink;
        $objFeed->podcastUrl = $strLink . 'share/' . $strFile . '.xml';
        $objFeed->title = $arrFeed['title'];
        $objFeed->subtitle = $arrFeed['subtitle'];
        $objFeed->description = self::cleanHtml($arrFeed['description']);
        $objFeed->explicit = $arrFeed['explicit'];
        $objFeed->language = $arrFeed['language'];
        $objFeed->author = $arrFeed['author'];
        $objFeed->owner = $arrFeed['owner'];
        $objFeed->email = $arrFeed['email'];
        $objFeed->category = $arrFeed['category'];
        $objFeed->published = $arrFeed['tstamp'];

        //Add Feed Image
        $objFile = \FilesModel::findByUuid($arrFeed['image']);

        if (null !== $objFile) {
            $objFeed->imageUrl = Environment::get('base') . $objFile->path;
        }

        // Get the items
        if ($arrFeed['maxItems'] > 0) {
            $objPodcasts = NewsPodcastsModel::findPublishedByPids(
                $arrArchives,
                null,
                $arrFeed['maxItems']
            );
        } else {
            $objPodcasts = NewsPodcastsModel::findPublishedByPids(
                $arrArchives
            );
        }

        // Parse the items
        if (null !== $objPodcasts) {
            $arrUrls = [];

            while ($objPodcasts->next()) {
                $jumpTo = $objPodcasts->getRelated('pid')->jumpTo;

                // No jumpTo page set (see #4784)
                if (!$jumpTo) {
                    continue;
                }

                // Get the jumpTo URL
                if (!isset($arrUrls[$jumpTo])) {
                    $objParent = PageModel::findWithDetails($jumpTo);

                    // A jumpTo page is set but does no longer exist (see #5781)
                    if (null === $objParent) {
                        $arrUrls[$jumpTo] = false;
                    } else {
                        $objUrlGenerator = System::getContainer()->get('contao.routing.url_generator');
                        $objUrlGenerator->generate(
                            ($objParent->alias ?: $objParent->id) . '/{items}',
                            [
                                'items' => 'example',
                                '_domain' => $objParent->domain,
                                '_ssl' => (bool) $objParent->rootUseSSL,
                            ],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );
                    }
                }

                // Skip the event if it requires a jumpTo URL but there is none
                if (false === $arrUrls[$jumpTo] && 'default' === $objPodcasts->source) {
                    continue;
                }

                $strUrl = $arrUrls[$jumpTo];
                $objItem = new \FeedItem();

                $objItem->headline = self::cleanHtml($objPodcasts->headline);
                $objItem->subheadline = self::cleanHtml(
                    (null !== $objPodcasts->subheadline) ? $objPodcasts->subheadline : $objPodcasts->description
                );
                $objItem->link = $strLink . sprintf(
                        $strUrl,
                        (('' !== $objPodcasts->alias
                          && !$GLOBALS['TL_CONFIG']['disableAlias']) ? $objPodcasts->alias : $objPodcasts->id)
                    );

                $objItem->published = $objPodcasts->date;
                $objAuthor = $objPodcasts->getRelated('author');
                $objItem->author = $objAuthor->name;
                $objItem->description = self::cleanHtml($objPodcasts->teaser);

                $objItem->explicit = $objPodcasts->explicit;

                // Add the article image as enclosure
                $objItem->addEnclosure($objFeed->imageUrl);

                // Add the Audio File
                if ($objPodcasts->podcast) {
                    $objFile = FilesModel::findByUuid($objPodcasts->podcast);

                    if (null !== $objFile) {
                        // Add statistics service
                        if (!empty($arrFeed['addStatistics'])) {
                            // If no trailing slash given, add one
                            $statisticsPrefix = rtrim($arrFeed['statisticsPrefix'], '/') . '/';
                            $podcastPath = $statisticsPrefix . Environment::get('host') . '/' . preg_replace(
                                    '(^https?://)',
                                    '',
                                    $objFile->path
                                );
                        } else {
                            $podcastPath = Environment::get('base') . System::urlEncode($objFile->path);
                        }

                        $objItem->podcastUrl = $podcastPath;

                        // Prepare the duration / prefer linux tool mp3info
                        $mp3file = new GetMp3Duration(TL_ROOT . '/' . $objFile->path);
                        if (self::checkMp3InfoInstalled()) {
                            $shell_command = 'mp3info -p "%S" ' . escapeshellarg(TL_ROOT . '/' . $objFile->path);
                            $duration = shell_exec($shell_command);
                        } else {
                            $duration = $mp3file->getDuration();
                        }

                        $objPodcastFile = new File($objFile->path, true);

                        $objItem->length = $objPodcastFile->size;
                        $objItem->type = $objPodcastFile->mime;
                        $objItem->duration = $mp3file->formatTime($duration);
                    }
                }

                $objFeed->addItem($objItem);
            }
        }

        // Create the file
        // $shareDir = \Contao\System::getContainer()->getParameter('contao.web_dir') . 'share/';
        $shareDir = 'web/share/';

        File::putContent(
            $shareDir . $strFile . '.xml',
            self::replaceInsertTags($objFeed->$strType())
        );
    }

    /**
     * Return the names of the existing feeds so they are not removed
     *
     * @return array
     */
    public function purgeOldFeeds()
    {
        $arrFeeds = array();
        $objFeeds = NewsPodcastsFeedModel::findAll();

        if ($objFeeds !== null)
        {
            while ($objFeeds->next())
            {
                $arrFeeds[] = $objFeeds->alias ?: 'news' . $objFeeds->id;
            }
        }
        dump($arrFeeds);

        return $arrFeeds;
    }


    /**
     * Check, if shell_exec and mp3info is callable.
     *
     * @return bool
     */
    protected function checkMp3InfoInstalled()
    {
        if (\is_callable('shell_exec') && false === stripos(ini_get('disable_functions'), 'shell_exec')) {
            $check = shell_exec('type -P mp3info');

            return (!empty($check)) ? true : false;
        }

        return false;
    }

    /**
     * @param $html
     *
     * @return string|string[]|null
     */
    protected function cleanHtml($html)
    {
        // remove P tags
        $html = preg_replace('/<p\b[^>]*>/i', '', $html);
        $html = preg_replace('/<\/p>/i', '', $html);

        // remove linebreaks
        $html = preg_replace('/[\n\r]+/i', '', $html);

        return $html;
    }
}
