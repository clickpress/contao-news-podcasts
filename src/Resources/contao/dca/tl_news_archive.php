<?php

/*
 * This file is part of NewsPodcasts.
 *
 * (c) Stefan Schulz-Lauterbach <ssl@clickpress.de>
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_news_archive']['list']['global_operations']['podcastfeeds'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['podcastfeeds'],
    'href' => 'table=tl_news_podcasts_feed',
    'class' => 'header_rss',
    'attributes' => 'onclick="Backend.getScrollOffset()"',
];
