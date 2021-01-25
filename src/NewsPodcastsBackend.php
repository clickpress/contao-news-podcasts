<?php

/*
 * This file is part of NewsPodcasts.
 *
 * (c) Stefan Schulz-Lauterbach <ssl@clickpress.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clickpress\NewsPodcasts;

use Clickpress\NewsPodcasts\Model\NewsPodcastsFeedModel;
use Contao\Config;
use Contao\DataContainer;
use Contao\Date;
use Contao\Input;
use Contao\News;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\Yaml\Yaml;

/**
 * Class NewsPodcastsBackend.
 */
class NewsPodcastsBackend extends \News
{
    /**
     * @var
     */
    private $arrItunesCategories;

    /**
     * Import the back end user object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Schedule a news feed update.
     *
     * This method is triggered when a single news item or multiple news
     * items are modified (edit/editAll), moved (cut/cutAll) or deleted
     * (delete/deleteAll). Since duplicated items are unpublished by default,
     * it is not necessary to schedule updates on copyAll as well.
     *
     * @param DataContainer
     */
    public function schedulePodcastUpdate(DataContainer $dc)
    {
        // Return if there is no ID
        if (!$dc->activeRecord || !$dc->activeRecord->pid || 'copy' === Input::get('act')) {
            return;
        }

        // Store the ID in the session
        /** @var Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
        $objSession = System::getContainer()->get('session');
        $session = $objSession->get('podcast_feed_updater');
        $session[] = $dc->activeRecord->pid;
        $objSession->set('podcast_feed_updater', array_unique($session));
    }

    /**
     * Add the type of input.
     *
     * @param array $arrRow
     *
     * @return string
     */
    public function listNewsPodcastArticles($arrRow)
    {
        $strHtml = $arrRow['headline'] .
                   ' <img src="bundles/newspodcasts/icon_mic.svg" width="16" height="16" alt="Podcast">';
        $arrRow['headline'] = ('1' === $arrRow['addPodcast']) ? $strHtml : $arrRow['headline'];

        return self::listNewsArticles($arrRow);
    }

    /**
     * List a news article.
     *
     * @param array $arrRow
     *
     * @return string
     */
    public function listNewsArticles($arrRow)
    {
        $date = Date::parse(Config::get('datimFormat'), $arrRow['date']);

        $html = '<div class="tl_content_left">'
                . $arrRow['headline']
                . ' <span style="color:#999;padding-left:3px">[' . $date . ']</span></div>';

        return $html;
    }

    /**
     * Check permissions to edit table tl_itunes_archive.
     */
    public function checkPermission()
    {
        if ($this->User->isAdmin) {
            return;
        }

        // Set the root IDs
        if (!\is_array($this->User->newspodcastsfeeds) || empty($this->User->newspodcastsfeeds)) {
            $root = [0];
        } else {
            $root = $this->User->newspodcastsfeeds;
        }

        $GLOBALS['TL_DCA']['tl_news_podcasts_feed']['list']['sorting']['root'] = $root;

        // Check permissions to add feeds
        if (!$this->User->hasAccess('create', 'newspodcastsfeedp')) {
            $GLOBALS['TL_DCA']['tl_news_podcasts_feed']['config']['closed'] = true;
        }

        // Check current action
        switch (Input::get('act')) {
            case 'create':
            case 'select':
                // Allow
                break;

            case 'edit':
                // Dynamically add the record to the user profile
                if (!\in_array(Input::get('id'), $root, true)) {
                    $arrNew = $this->Session->get('new_records');

                    if (\is_array($arrNew['tl_news_podcasts_feed']) && \in_array(Input::get('id'),
                                                                                  $arrNew['tl_news_podcasts_feed'], true)
                    ) {
                        // Add permissions on user level
                        if ('custom' === $this->User->inherit || !$this->User->groups[0]) {
                            $objUser = $this->Database->prepare('SELECT newspodcastsfeeds, newspodcastsfeedp FROM tl_user WHERE id=?')
                                ->limit(1)
                                ->execute($this->User->id);

                            $arrnewspodcastsfeedp = StringUtil::deserialize($objUser->newspodcastsfeedp);

                            if (\is_array($arrnewspodcastsfeedp) && \in_array('create', $arrnewspodcastsfeedp, true)) {
                                $arrnewspodcastsfeeds = StringUtil::deserialize($objUser->newspodcastsfeeds);
                                $arrnewspodcastsfeeds[] = Input::get('id');

                                $this->Database->prepare('UPDATE tl_user SET newspodcastsfeeds=? WHERE id=?')
                                    ->execute(serialize($arrnewspodcastsfeeds), $this->User->id);
                            }
                        } // Add permissions on group level
                        elseif ($this->User->groups[0] > 0) {
                            $objGroup = $this->Database->prepare('SELECT newspodcastsfeeds, newspodcastsfeedp FROM tl_user_group WHERE id=?')
                                ->limit(1)
                                ->execute($this->User->groups[0]);

                            $arrnewspodcastsfeedp = StringUtil::deserialize($objGroup->newspodcastsfeedp);

                            if (\is_array($arrnewspodcastsfeedp) && \in_array('create', $arrnewspodcastsfeedp, true)) {
                                $arrnewspodcastsfeeds = StringUtil::deserialize($objGroup->newspodcastsfeeds);
                                $arrnewspodcastsfeeds[] = Input::get('id');

                                $this->Database->prepare('UPDATE tl_user_group SET newspodcastsfeeds=? WHERE id=?')
                                    ->execute(serialize($arrnewspodcastsfeeds), $this->User->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[] = Input::get('id');
                        $this->User->newspodcastsfeeds = $root;
                    }
                }
            // no break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!\in_array(Input::get('id'),
                               $root, true) || ('delete' === Input::get('act') && !$this->User->hasAccess('delete',
                                                                                                       'newspodcastsfeedp'))
                ) {
                    $this->log('Not enough permissions to ' . Input::get('act') . ' podcast feed ID "' . Input::get('id') . '"',
                                __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if ('deleteAll' === Input::get('act') && !$this->User->hasAccess('delete', 'newspodcastsfeedp')) {
                    $session['CURRENT']['IDS'] = [];
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $this->Session->setData($session);
                break;

            default:
                if (\strlen(Input::get('act'))) {
                    $this->log('Not enough permissions to ' . Input::get('act') . ' podcast feeds', __METHOD__,
                                TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;
        }
    }

    /**
     * Check for modified itunes feeds and update the XML files if necessary.
     */
    public function generatePodcastFeed()
    {
        /** @var Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
        $objSession = System::getContainer()->get('session');
        $session = $objSession->get('podcasts_feed_updater');

        if (!\is_array($session) || empty($session)) {
            return;
        }

        $feed = new NewsPodcasts();

        NewsPodcasts::generateFeeds();

        $objSession->set('podcasts_feed_updater', null);
    }

    /**
     * Schedule a itunes feed update.
     *
     * This method is triggered when a single itunes archive or multiple itunes
     * archives are modified (edit/editAll).
     *
     * @param \DataContainer
     */
    public function scheduleUpdate(\DataContainer $dc)
    {
        // Return if there is no ID
        if (!$dc->id) {
            return;
        }

        /** @var Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
        $objSession = System::getContainer()->get('session');

        // Store the ID in the session
        $session = $objSession->get('podcasts_feed_updater');
        $session[] = $dc->id;
        $objSession->set('podcasts_feed_updater', array_unique($session));
    }

    /**
     * Return the IDs of the allowed itunes archives as array.
     *
     * @return array
     */
    public function getAllowedArchives()
    {
        if ($this->User->isAdmin) {
            $objArchive = \NewsArchiveModel::findAll();
        } else {
            $objArchive = \NewsArchiveModel::findMultipleByIds($this->User->news);
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
    public function preservePodcastFeeds()
    {
        $objFeeds = NewsPodcastsFeedModel::findAll();
        while ($objFeeds->next()) {
            $arrFeeds[] = $objFeeds->alias ?: 'news' . $objFeeds->id;
        }

        return $arrFeeds;
    }

    /**
     * Check the RSS-feed alias.
     *
     * @param mixed
     * @param \DataContainer
     * @param mixed $varValue
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function checkFeedAlias($varValue, \DataContainer $dc)
    {
        // No change or empty value
        if ($varValue === $dc->value || '' === $varValue) {
            return $varValue;
        }

        $varValue = standardize($varValue); // see #5096

        $this->import('Automator');
        $arrFeeds = $this->Automator->purgeXmlFiles(true);

        // Alias exists
        if (false !== array_search($varValue, $arrFeeds, true)) {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        return $varValue;
    }

    /**
     * Get all categories from yaml https://github.com/mr-rigden/Podcast-Categories-List.
     *
     * @return array
     */
    public function getItunesCategories()
    {
        $categories = Yaml::parseFile(__DIR__ . '/Resources/config/podcast_categories_list.yaml');

        foreach ($categories as $k => $v) {
            $this->arrItunesCategories[$v['category']] = [];
            if (\is_array($v['subcategories'])) {
                foreach ($v['subcategories'] as $sub) {
                    $this->arrItunesCategories[$v['category']][$v['category'] . '|' . $sub] = $sub;
                }
            }
        }

        return $this->arrItunesCategories;
    }

    public function checkNewsCategoriesBundle()
    {
        $arrBundles = System::getContainer()->getParameter('kernel.bundles');

        return array_key_exists("CodefogNewsCategoriesBundle", $arrBundles);
    }
}
