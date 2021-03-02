<?php

declare(strict_types=1);

/*
 * This file is part of NewsPodcasts.
 *
 * (c) Stefan Schulz-Lauterbach <ssl@clickpress.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clickpress\NewsPodcasts;

use Clickpress\NewsPodcasts\Helper\GetMp3Duration;
use Clickpress\NewsPodcasts\Helper\PodcastFeedHelper;
use Clickpress\NewsPodcasts\Model\NewsPodcastsFeedModel;
use Clickpress\NewsPodcasts\Model\NewsPodcastsModel;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Environment;
use Contao\File;
use Contao\Files;
use Contao\FilesModel;
use Contao\Frontend;
use Contao\Input;
use Contao\PageModel;
use Contao\System;
use Exception;
use Psr\Log\LogLevel;
use StringUtil;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class NewsPodcasts.
 *
 * @copyright  CLICKPRESS Internetagentur 2021
 * @author     Stefan Schulz-Lauterbach
 *
 * @property string $podcastUrl Url to podcast file
 * @property string $subtitle   Feed meta - subtitle
 * @property string $explicit   Explicit Content Flagging
 * @property string $author     Feed author
 * @property string $owner      Feed owner
 * @property string $email      Email address of the owner / author
 * @property string $category   Feed category
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
    public function generateFeed($intId): void
    {
        $objFeed = NewsPodcastsFeedModel::findByArchive($intId);

        if (null !== $objFeed) {
            $objFeed->feedName = $objFeed->alias ?: 'podcast_' . $objFeed->id;

            // Delete XML file
            if ('delete' === Input::get('act')) {
                Files::getInstance()->delete($objFeed->feedName . '.xml');
            } // Update XML file
            else {
                $this->generateFiles($objFeed->row());
                $logger = System::getContainer()->get('monolog.logger.contao');
                $logger->log(LogLevel::INFO, 'Generated podcast feed "' . $objFeed->feedName . '.xml"', ['contao' => new ContaoContext(__METHOD__, TL_CRON)]);
            }
        }
    }

    /**
     * Delete old files and generate all feeds.
     *
     * @throws Exception
     */
    public function generateFeeds(): void
    {
        $logger = \System::getContainer()->get('monolog.logger.contao');

        $objFeed = NewsPodcastsFeedModel::findAll();

        if (null !== $objFeed) {
            while ($objFeed->next()) {
                $objFeed->feedName = $objFeed->alias ?: 'podcast_' . $objFeed->id;
                self::generateFiles($objFeed->row());
                $logger->log(
                    LogLevel::INFO,
                    'Generated podcast feed "' . $objFeed->feedName . '.xml"',
                    ['contao' => new ContaoContext(__METHOD__, ContaoContext::CRON)]
                );
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
    public function generateFeedsByArchive($intId): void
    {
        $objFeed = NewsPodcastsFeedModel::findByArchive($intId);

        if (null !== $objFeed) {
            while ($objFeed->next()) {
                $objFeed->feedName = $objFeed->alias ?: 'podcast_' . $objFeed->id;
                // Update the XML file
                self::generateFiles($objFeed->row());
                $logger = \System::getContainer()->get('monolog.logger.contao');
                $logger->log(
                    LogLevel::INFO,
                    'Generated podcast feed "' . $objFeed->feedName . '.xml"',
                    ['contao' => new ContaoContext(__METHOD__, TL_CRON)]
                );
            }
        }
    }

    /**
     * Return the names of the existing feeds so they are not removed.
     */
    public function purgeOldFeeds(): array
    {
        $arrFeeds = [];
        $objFeeds = NewsPodcastsFeedModel::findAll();

        if (null !== $objFeeds) {
            while ($objFeeds->next()) {
                $arrFeeds[] = $objFeeds->alias ?: 'podcast_' . $objFeeds->id;
            }
        }

        return $arrFeeds;
    }

    /**
     * @param $arrFeed
     *
     * @throws Exception
     */
    protected static function generateFiles($arrFeed): void
    {
        $arrArchives = StringUtil::deserialize($arrFeed['archives']);

        if (!\is_array($arrArchives) || empty($arrArchives)) {
            return;
        }

        $strType = 'generatePodcastFeed';

        $strLink = $arrFeed['feedBase'] ?: Environment::get('base');
        $strFile = $arrFeed['feedName'];

        $objFeed = new PodcastFeedHelper($strFile);
        $objFeed->link = rtrim($strLink, '/\\'); // Fix trailing slash https://github.com/clickpress/contao-news-podcasts/issues/4
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

        $objDateTime = new \DateTime();
        $objFeed->lastBuildDate = $objDateTime->format(\DateTime::ATOM);

        //Add Feed Image
        $objFile = \FilesModel::findByUuid($arrFeed['image']);

        if (null !== $objFile) {
            $objFeed->imageUrl = Environment::get('base') . $objFile->path;
        }

        // Add filter, if newsCategories is installed
        $arrOptions = [];
        $arrColumns = [];
        if (null !== $arrFeed['news_categoriesRoot'] && NewsPodcastsBackend::checkNewsCategoriesBundle()) {
            $db = System::getContainer()->get('database_connection');
            $arrResult = $db->executeQuery('SELECT news_id FROM tl_news_categories WHERE category_id = ?', [$arrFeed['news_categoriesRoot']])->fetchAll();

            $arrNewsId = [];
            foreach ($arrResult as $id) {
                $arrNewsId['id'][] = $id['news_id'];
            }

            if (null !== $arrNewsId) {
                $arrColumns[] = 'id IN(' . \implode(',', $arrNewsId['id']) . ')';
            }
        }

        // Get the items
        if ($arrFeed['maxItems'] > 0) {
            $objPodcasts = NewsPodcastsModel::findPublishedByPids(
                $arrArchives,
                null,
                $arrFeed['maxItems'],
                0,
                $arrColumns,
                $arrOptions
            );
        } else {
            $objPodcasts = NewsPodcastsModel::findPublishedByPids(
                $arrArchives,
                null,
                0,
                0,
                $arrColumns,
                $arrOptions
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

                $objItem->id = (int) $objPodcasts->id;
                $objItem->guid = (int) $objPodcasts->id;
                $objItem->alias = $objPodcasts->alias;
                $objItem->time = $objPodcasts->time;
                $objItem->headline = self::cleanHtml($objPodcasts->headline);
                $objItem->subheadline = self::cleanHtml(
                    $objPodcasts->subheadline ?? $objPodcasts->description
                );
                $objItem->link = $strLink . sprintf(
                        $strUrl,
                        (('' !== $objPodcasts->alias && !$GLOBALS['TL_CONFIG']['disableAlias']) ? $objPodcasts->alias : $objPodcasts->id)
                    );

                $objDateTime = new \DateTime();
                $objItem->published = $objDateTime->setTimestamp((int) $objPodcasts->date)->format(\DateTime::ATOM);
                $objAuthor = $objPodcasts->getRelated('author');
                $objItem->author = $objAuthor->name;
                $objItem->teaser = self::cleanHtml($objPodcasts->teaser ?? $objPodcasts->description);

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
                        $strRoot = System::getContainer()->getParameter('kernel.project_dir');
                        $mp3file = new GetMp3Duration($strRoot . '/' . $objFile->path);
                        if (self::checkMp3InfoInstalled()) {
                            $shell_command = 'mp3info -p "%S" ' . escapeshellarg($strRoot . '/' . $objFile->path);
                            $duration = (int) shell_exec($shell_command);

                            if (0 === $duration) {
                                $duration = $mp3file->getDuration();
                            }
                        } else {
                            $duration = $mp3file->getDuration();
                        }

                        $objPodcastFile = new File($objFile->path);

                        $objItem->length = $objPodcastFile->size;
                        $objItem->type = $objPodcastFile->mime;
                        $objItem->duration = GetMp3Duration::formatTime($duration);
                    }
                }

                $objFeed->addItem($objItem);
            }
        }

        // Create the file
        $shareDir = 'web/share/';
        // $shareDir = System::getContainer()->getParameter('contao.web_dir') . '/share/';

        File::putContent(
            $shareDir . $strFile . '.xml',
            self::replaceInsertTags($objFeed->$strType())
        );
    }

    /**
     * Check, if shell_exec and mp3info is callable.
     */
    protected static function checkMp3InfoInstalled(): bool
    {
        if (\is_callable('shell_exec') && false === stripos(ini_get('disable_functions'), 'shell_exec')) {
            $check = shell_exec('type -P mp3info');

            return !empty($check);
        }

        return false;
    }

    /**
     * Remove unwanted HTML tags.
     *
     * @param $strHtml
     */
    protected static function cleanHtml($strHtml): string
    {
        // remove P tags
        $strHtml = preg_replace('/<p\b[^>]*>/i', '', $strHtml);
        $strHtml = preg_replace('/<\/p>/i', '', $strHtml);

        // remove linebreaks
        $strHtml = preg_replace('/[\n\r]+/i', '', $strHtml);

        return $strHtml;
    }
}

//class_alias(NewsPodcasts::class, 'Feed');
