<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package   news_podcasts
 * @author    Stefan Schulz-Lauterbach
 * @license   GNU/LGPL
 * @copyright CLICKPRESS Internetagentur 2015
 */


$GLOBALS['BE_MOD']['content']['news']['tables'][] = 'tl_news_podcasts_feed';

/**
 * Cron jobs
 */
// $GLOBALS['TL_CRON']['daily'][] = array( 'NewsPodcasts', 'generateFeeds' );

/**
 * Register hook to add news items to the indexer
 */
$GLOBALS['TL_HOOKS']['generateXmlFiles'][] = array( 'Clickpress\NewsPodcasts\NewsPodcastsBackend', 'generatePodcastFeed' );
$GLOBALS['TL_HOOKS']['removeOldFeeds'][] = array( 'Clickpress\NewsPodcasts\NewsPodcastsBackend', 'preservePodcastFeeds' );

$GLOBALS['TL_MODELS']['tl_news'] = 'Clickpress\NewsPodcasts\Model\NewsPodcastsModel';
$GLOBALS['TL_MODELS']['tl_news_podcasts_feed'] = 'Clickpress\NewsPodcasts\Model\NewsPodcastsFeedModel';


/**
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'newspodcastsfeeds';
$GLOBALS['TL_PERMISSIONS'][] = 'newspodcastsfeedp';
