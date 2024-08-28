<?php

namespace Clickpress\NewsPodcasts\Frontend;

use Clickpress\NewsPodcasts\Backend\NewsPodcastsBackend;
use Clickpress\NewsPodcasts\Helper\GetMp3Duration;
use Clickpress\NewsPodcasts\Helper\PodcastFeedHelper;
use Clickpress\NewsPodcasts\Model\NewsPodcastsFeedModel;
use Clickpress\NewsPodcasts\Model\NewsPodcastsModel;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Environment;
use Contao\FeedItem;
use Contao\File;
use Contao\Files;
use Contao\FilesModel;
use Contao\Frontend;
use Contao\Image\ResizeConfiguration;
use Contao\Input;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use DateTimeInterface;
use Exception;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NewsPodcastsFrontend extends Frontend
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
                self::generateFiles($objFeed->row());

                $this->logInfo('Generated podcast feed "' . $objFeed->feedName . '.xml"');

            }
        }
    }

    /**
     * Delete old files and generate all feeds.
     *
     * @throws Exception
     */
    public static function generateFeeds(): void
    {

        $objFeed = NewsPodcastsFeedModel::findAll();

        if (null === $objFeed) {
            return;
        }

        while ($objFeed->next()) {
            $objFeed->feedName = $objFeed->alias ?: 'podcast_' . $objFeed->id;
            self::generateFiles($objFeed->row());
        }
    }

    public function logInfo(string $text): void
    {
        $logger = System::getContainer()->get('monolog.logger.contao');
        $logger?->info(
            $text,
            ['contao' => new ContaoContext(__METHOD__, ContaoContext::GENERAL)]
        );
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

        if (null === $objFeed) {
            return;
        }

        while ($objFeed->next()) {
            $objFeed->feedName = $objFeed->alias ?: 'podcast_' . $objFeed->id;
            // Update the XML file
            self::generateFiles($objFeed->row());
            $this->logInfo('Generated podcast feed "' . $objFeed->feedName . '.xml"');
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

        $strLink = $arrFeed['feedBase'] ?: Environment::get('base');
        $strFile = $arrFeed['feedName'];

        $objFeed = new PodcastFeedHelper($strFile);

        // Podcasts meta
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
        $objFeed->lastBuildDate = $objDateTime->format(DateTimeInterface::RFC2822);

        //Add Feed Image
        $objFile = FilesModel::findByUuid($arrFeed['image']);

        if (null !== $objFile) {
            $objFeed->imageUrl = Environment::get('base') . $objFile->path;
        }

        // Add filter, if newsCategories is installed
        $arrOptions = [];
        $arrColumns = [];
        $newscategoriesRoot = $arrFeed['news_categoriesRoot'] ?? null;

        if (null !== $newscategoriesRoot && NewsPodcastsBackend::checkNewsCategoriesBundle()) {
            $db = System::getContainer()->get('database_connection');
            $arrResult = $db?->executeQuery(
                'SELECT news_id FROM tl_news_categories WHERE category_id = ?',
                [$arrFeed['news_categoriesRoot']]
            )->fetchAllAssociative();

            $arrNewsId = [];
            foreach ($arrResult as $id) {
                $arrNewsId['id'][] = $id['news_id'];
            }

            if (!empty($arrNewsId['id'])) {
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

        if (null === $objPodcasts) {
            return;
        }



        // Podcast items

        $arrUrls = [];

        while ($objPodcasts->next()) {
            $jumpTo = $objPodcasts->getRelated('pid')->jumpTo;

            // No jumpTo page set (see #4784)
            if (!$jumpTo) {
                continue;
            }

            // Get the jumpTo URL
            $objParent = PageModel::findWithDetails($jumpTo);
            // A jumpTo page is set but does no longer exist (see #5781)
            if ($objParent === null)
            {
                continue;
            }

            $urlGenerator = System::getContainer()->get('contao.routing.content_url_generator');
            $arrUrls[$jumpTo] = $urlGenerator?->generate(
                $objPodcasts->current(),
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $strUrl = $arrUrls[$jumpTo];
            $objItem = new FeedItem();

            $objItem->id = (int) $objPodcasts->id;
            $objItem->guid = (int) $objPodcasts->id;
            $objItem->alias = $objPodcasts->alias;
            $objItem->time = $objPodcasts->time;
            $objItem->headline = self::cleanHtml($objPodcasts->headline);
            $objItem->subheadline = self::cleanHtml(
                $objPodcasts->subheadline ?? $objPodcasts->description
            );

            // Add episode image
            if (null !== $objPodcasts->singleSRC && $arrFeed['useEpisodeImage']) {
                $objItem->image = self::generateEpisodeImage($objPodcasts->singleSRC);
            }

            $objItem->link = sprintf(
                $strUrl,
                (('' !== $objPodcasts->alias) ? $objPodcasts->alias : $objPodcasts->id)
            );

            $objDateTime = new \DateTime();
            $objItem->published = $objDateTime->setTimestamp((int) $objPodcasts->date)->format(DateTimeInterface::RFC2822);
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

        // Create the file
        $container = System::getContainer();
        $shareDir = StringUtil::stripRootDir($container->getParameter('contao.web_dir')) . '/share/';
        $parser = $container->get('contao.insert_tag.parser');

        File::putContent(
            $shareDir . $strFile . '.xml',
            // replace insert tags
            $parser?->replace((string) $objFeed->generatePodcastFeed())
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
     */
    protected static function cleanHtml($strHtml): string
    {
        // remove P tags
        $strHtml = preg_replace('/<p\b[^>]*>/i', '', $strHtml);
        $strHtml = preg_replace('/<\/p>/i', '', $strHtml);

        // remove linebreaks
        return preg_replace('/[\n\r]+/i', '', $strHtml);
    }

    /**
     * Generate episode image.
     */
    protected static function generateEpisodeImage($singleSrc): string
    {
        $objFile = FilesModel::findByUuid($singleSrc);

        if (null === $objFile) {
            return '';
        }

        $container = System::getContainer();
        $rootDir = $container->getParameter('kernel.project_dir');
        $episodeImg = $container
            ->get('contao.image_factory')
            ?->create(
                $rootDir . '/' . $objFile->path,
                (new ResizeConfiguration())
                    ->setWidth(1400)
                    ->setHeight(1400)
                    ->setMode(ResizeConfiguration::MODE_CROP)
                    ->setZoomLevel(50)
                )
            ->getUrl($rootDir);

        return Environment::get('url') . '/' . $episodeImg;
    }

    public function getSlug(string $text, string $locale = 'en', string $validChars = '0-9a-z'): string
    {
        $options = [
            'locale' => $locale,
            'validChars' => $validChars,
        ];

        return $this->slug->generate($text, $options);
    }
}
