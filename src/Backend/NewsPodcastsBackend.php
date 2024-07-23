<?php

namespace Clickpress\NewsPodcasts\Backend;

use Clickpress\NewsPodcasts\Frontend\NewsPodcastsFrontend;
use Clickpress\NewsPodcasts\Model\NewsPodcastsFeedModel;
use Contao\BackendUser;
use Contao\Config;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\DataContainer;
use Contao\Date;
use Contao\Input;
use Contao\News;
use Contao\NewsArchiveModel;
use Contao\StringUtil;
use Contao\System;
use RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class NewsPodcastsBackend.
 */
class NewsPodcastsBackend extends News
{
    private array $arrItunesCategories;

    /**
     * Add the type of input.
     *
     * @param array $arrRow
     *
     * @return string
     */
    public function listNewsPodcastArticles(array $arrRow): string
    {
        $strHtml = $arrRow['headline'] .
                   ' <img src="bundles/newspodcasts/icon_mic.svg" width="16" height="16" alt="Podcast">';
        $arrRow['headline'] = ('1' === $arrRow['addPodcast']) ? $strHtml : $arrRow['headline'];

        return $this->listNewsArticles($arrRow);
    }

    /**
     * List a news article.
     *
     * @param array $arrRow
     *
     * @return string
     */
    public function listNewsArticles(array $arrRow): string
    {
        $date = Date::parse(Config::get('datimFormat'), $arrRow['date']);

        return '<div class="tl_content_left">'
                . $arrRow['headline']
                . ' <span style="color:#999;padding-left:3px">[' . $date . ']</span></div>';
    }



    /**
     * Schedule a podcast feed update.
     *
     * This method is triggered when a single news item or multiple news
     * items are modified (edit/editAll), moved (cut/cutAll) or deleted
     * (delete/deleteAll). Since duplicated items are unpublished by default,
     * it is not necessary to schedule updates on copyAll as well.
     *
     * @param DataContainer
     */
    public function schedulePodcastUpdate(DataContainer $dc): void
    {
        // Return if there is no ID
        if (!$dc->activeRecord || !$dc->activeRecord->id || 'copy' === Input::get('act')) {
            return;
        }

        // Store the ID in the session
        $objSession = System::getContainer()->get('request_stack')->getSession();
        $session = $objSession->get('podcasts_feed_updater');
        $session[] = $dc->activeRecord->id;
        $objSession->set('podcasts_feed_updater', array_unique($session));
    }

    /**
     * Check for modified itunes feeds and update the XML files if necessary.
     */
    public function generatePodcastFeed(): void
    {
        $session = System::getContainer()->get('request_stack')->getSession();
        $feedUpdater = $session->get('podcasts_feed_updater');

        if (!is_array($feedUpdater) || empty($feedUpdater)) {
            return;
        }

        NewsPodcastsFrontend::generateFeeds();


        $session->set('podcasts_feed_updater', null);
    }

    /**
     * Return the IDs of the allowed itunes archives as array.
     *
     * @return array
     */
    public function getAllowedArchives(): array
    {
        $user = BackendUser::getInstance();
        if (BackendUser::getInstance()->isAdmin) {
            $objArchive = NewsArchiveModel::findAll();
        } else {
            $objArchive = NewsArchiveModel::findMultipleByIds($user->news);
        }

        $return = [];

        if (null !== $objArchive) {
            while ($objArchive->next()) {
                $return[$objArchive->id] = $objArchive->title;
            }
        }

        return $return;
    }



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

        while ($objFeeds->next()) {
            $arrFeeds[] = $objFeeds->alias ?: 'news' . $objFeeds->id;
        }

        return $arrFeeds;
    }

    /**
     * Check the RSS-feed alias.
     */
    public function checkFeedAlias(string $varValue, DataContainer $dc): mixed
    {
        // No change or empty value
        if ($varValue === $dc->value || '' === $varValue) {
            return $varValue;
        }

        $varValue = StringUtil::standardize($varValue); // see #5096

        $this->import('Automator');
        $arrFeeds = $this->Automator->purgeXmlFiles(true);

        // Alias exists
        if (in_array($varValue, $arrFeeds, true)) {
            throw new RuntimeException(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        return $varValue;
    }

    /**
     * Get all categories from yaml https://github.com/mr-rigden/Podcast-Categories-List.
     *
     * @return array
     */
    public function getItunesCategories(): array
    {
        $categories = Yaml::parseFile(__DIR__ . '/../../config/podcast_categories_list.yaml');

        foreach ($categories as $v) {
            $this->arrItunesCategories[$v['category']] = [];
            if (is_array($v['subcategories'])) {
                foreach ($v['subcategories'] as $sub) {
                    $this->arrItunesCategories[$v['category']][$v['category'] . '|' . $sub] = $sub;
                }
            }
        }

        return $this->arrItunesCategories;
    }

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
