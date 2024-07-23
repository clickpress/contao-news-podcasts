<?php

use Clickpress\NewsPodcasts\Backend\NewsPodcastsBackend;
use Clickpress\NewsPodcasts\Model\NewsPodcastsFeedModel;
use Clickpress\NewsPodcasts\Model\NewsPodcastsModel;

$GLOBALS['BE_MOD']['content']['news']['tables'][] = 'tl_news_podcasts_feed';

/*
 * Cron jobs
 */
$GLOBALS['TL_CRON']['daily'][] = [NewsPodcastsBackend::class, 'generatePodcastFeed'];

$GLOBALS['TL_MODELS']['tl_news'] = NewsPodcastsModel::class;
$GLOBALS['TL_MODELS']['tl_news_podcasts_feed'] = NewsPodcastsFeedModel::class;

/*
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'newspodcastsfeeds';
$GLOBALS['TL_PERMISSIONS'][] = 'newspodcastsfeedp';
