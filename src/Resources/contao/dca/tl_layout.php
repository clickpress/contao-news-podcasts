<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Extend default palette
PaletteManipulator::create()
    ->addField('podcastfeeds', 'feed_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_layout')
;

// Extend fields
$GLOBALS['TL_DCA']['tl_layout']['fields']['podcastfeeds'] = [
    'exclude'         => true,
    'inputType'       => 'checkbox',
    'foreignKey'      => 'tl_news_podcasts_feed.title',
    'eval'            => array('multiple'=>true),
    'sql'             => "blob NULL",
];
