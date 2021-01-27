<?php

/*
 * This file is part of NewsPodcasts.
 *
 * (c) Stefan Schulz-Lauterbach <ssl@clickpress.de>
 *
 * @license LGPL-3.0-or-later
 */
use Clickpress\NewsPodcasts\NewsPodcastsBackend;

$GLOBALS['TL_DCA']['tl_news']['config']['onload_callback'][] = [
    NewsPodcastsBackend::class,
    'generatePodcastFeed'
];

$GLOBALS['TL_DCA']['tl_news']['config']['oncut_callback'][] = [
    NewsPodcastsBackend::class,
    'schedulePodcastUpdate'
];
$GLOBALS['TL_DCA']['tl_news']['config']['ondelete_callback'][] = [
    NewsPodcastsBackend::class,
    'schedulePodcastUpdate'
];
$GLOBALS['TL_DCA']['tl_news']['config']['onsubmit_callback'][] = [
    NewsPodcastsBackend::class,
    'schedulePodcastUpdate'
];
$GLOBALS['TL_DCA']['tl_news']['list']['sorting']['child_record_callback'] = [
    NewsPodcastsBackend::class,
    'listNewsPodcastArticles',
];
$GLOBALS['TL_DCA']['tl_news']['palettes']['default'] = str_replace(
    'source;',
    'source;{podcast_legend},addPodcast;',
    $GLOBALS['TL_DCA']['tl_news']['palettes']['default']
);
$GLOBALS['TL_DCA']['tl_news']['palettes']['__selector__'][] = 'addPodcast';
$GLOBALS['TL_DCA']['tl_news']['subpalettes']['addPodcast'] = 'podcast,explicit';

$GLOBALS['TL_DCA']['tl_news']['fields']['addPodcast'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news']['addPodcast'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_news']['fields']['podcast'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news']['podcast'],
    'exclude' => true,
    'inputType' => 'fileTree',
    'eval' => ['filesOnly' => true, 'extensions' => 'mp3', 'fieldType' => 'radio', 'mandatory' => true],
    'sql' => 'binary(16) NULL',
];

$GLOBALS['TL_DCA']['tl_news']['fields']['explicit'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news']['explicit'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'select',
    'options' => ['no', 'clean', 'yes'],
    'default' => 'no',
    'eval' => ['chosen' => true, 'includeBlankOption' => true],
    'sql' => "varchar(255) NOT NULL default ''",
];
