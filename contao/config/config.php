<?php

/*
 * This file is part of NewsPodcasts.
 *
 * (c) Stefan Schulz-Lauterbach <ssl@clickpress.de>
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['BE_MOD']['content']['news']['tables'][] = 'tl_news_podcasts_feed';

/*
 * Cron jobs
 */
$GLOBALS['TL_CRON']['daily'][] = ['Clickpress\NewsPodcasts\NewsPodcastsBackend', 'generatePodcastFeed'];

$GLOBALS['TL_MODELS']['tl_news'] = 'Clickpress\NewsPodcasts\Model\NewsPodcastsModel';
$GLOBALS['TL_MODELS']['tl_news_podcasts_feed'] = 'Clickpress\NewsPodcasts\Model\NewsPodcastsFeedModel';

/*
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'newspodcastsfeeds';
$GLOBALS['TL_PERMISSIONS'][] = 'newspodcastsfeedp';
