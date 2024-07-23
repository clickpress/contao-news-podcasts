<?php

$GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace('newsfeedp;', 'newsfeedp,newspodcastsfeeds,newspodcastsfeedp;', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace('newsfeedp;', 'newsfeedp,newspodcastsfeeds,newspodcastsfeedp;', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);

$GLOBALS['TL_DCA']['tl_user']['fields']['newspodcastsfeeds'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['newspodcastsfeeds'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_news_podcasts_feed.title',
    'eval' => ['multiple' => true],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['newspodcastsfeedp'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['newspodcastsfeedp'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true],
    'sql' => 'blob NULL',
];
