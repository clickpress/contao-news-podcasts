<?php

if ('news' === \Contao\Input::get('do')) {
    $GLOBALS['TL_DCA']['tl_content']['list']['sorting']['headerFields'][] = 'addPodcast';
    $GLOBALS['TL_DCA']['tl_content']['list']['sorting']['headerFields'][] = 'podcast';
}

