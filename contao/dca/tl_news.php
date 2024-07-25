<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addLegend('podcast_legend','source_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('addPodcast', 'podcast_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_news');


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
