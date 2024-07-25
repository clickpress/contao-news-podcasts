<?php

// @ToDo: Use palette manipulator
$GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace('newsfeedp;', 'newsfeedp,newspodcastsfeeds,newspodcastsfeedp;', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);

/*
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['newspodcastsfeeds'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['newspodcastsfeeds'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_news_podcasts_feed.title',
    'eval' => ['multiple' => true],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['newspodcastsfeedp'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['newspodcastsfeedp'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true],
    'sql' => 'blob NULL',
];
