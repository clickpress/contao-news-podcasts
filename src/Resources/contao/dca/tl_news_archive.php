<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   cp_podcasts
 * @author    Stefan Schulz-Lauterbach
 * @license   GNU/LGPL
 * @copyright CLICKPRESS Internetagentur 2015
 */

$GLOBALS['TL_DCA']['tl_news_archive']['list']['global_operations']['podcastfeeds'] = array
(
    'label'      => &$GLOBALS['TL_LANG']['tl_news_archive']['podcastfeeds'],
    'href'       => 'table=tl_news_podcasts_feed',
    'class'      => 'header_rss',
    'attributes' => 'onclick="Backend.getScrollOffset()"'
);