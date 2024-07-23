<?php

$GLOBALS['TL_DCA']['tl_news_archive']['list']['global_operations']['podcastfeeds'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['podcastfeeds'],
    'href' => 'table=tl_news_podcasts_feed',
    'class' => 'header_rss',
    'attributes' => 'onclick="Backend.getScrollOffset()"',
];
