<?php

use Clickpress\NewsPodcasts\Backend\NewsPodcastsBackend;
use Contao\DC_Table;
use Contao\Environment;

$GLOBALS['TL_DCA']['tl_news_podcasts_feed'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'onload_callback' => [
            [NewsPodcastsBackend::class, 'checkNewsCategoriesBundle'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'alias' => 'index',
            ],
        ],
        'backlink' => 'do=news',
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['title'],
            'flag' => 1,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit',
            'copy',
            'delete',
            'show'
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{title_legend},title,alias,language;{description_legend},subtitle,description,category,explicit;{author_legend},author,owner,email,copyright;{image_legend},image,useEpisodeImage;{archives_legend},archives,news_categoriesRoot;{config_legend},maxItems,feedBase;{statistic_legend},addStatistics;',
        '__selector__' => ['addStatistics'],
    ],

    // Subpalettes
    'subpalettes' => [
        'addStatistics' => 'statisticsPrefix',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'alias' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'rgxp' => 'alias',
                'unique' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
            ],
            'sql' => "varchar(255) BINARY NOT NULL default ''"
        ],
        'language' => [
            'exclude' => true,
            'search' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 32, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'subtitle' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'long'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'summary' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'description' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['style' => 'height:60px', 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'category' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'eval' => ['chosen' => true, 'multiple' => true, 'isAssociative' => true, 'mandatory' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'explicit' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => ['no', 'clean', 'yes'],
            'default' => 'no',
            'eval' => ['chosen' => true, 'includeBlankOption' => false],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'owner' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'email' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'email', 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'author' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'image' => [
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => [
                'fieldType' => 'radio',
                'filesOnly' => true,
                'extensions' => 'jpg,png',
                'mandatory' => true,
                'tl_class' => 'w50',
            ],
            'sql' => 'blob NULL',
        ],
        'useEpisodeImage' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'archives' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'checkbox',
            'eval' => ['multiple' => true, 'mandatory' => true],
            'sql' => 'blob NULL',
        ],
        'maxItems' => [
            'default' => 25,
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => "smallint(5) unsigned NOT NULL default '0'",
        ],
        'feedBase' => [
            'default' => Environment::get('base'),
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['trailingSlash' => true,
                                  'rgxp' => 'url',
                                  'decodeEntities' => true,
                                  'maxlength' => 255,
                                  'tl_class' => 'w50',
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'addStatistics' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'statisticsPrefix' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'default' => '',
            'eval' => ['trailingSlash' => true,
                                  'rgxp' => 'url',
                                  'decodeEntities' => true,
                                  'maxlength' => 255,
                                  'tl_class' => 'w50',
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
    ],
];

// Inject, if NewsCategories is installed
if(NewsPodcastsBackend::checkNewsCategoriesBundle()){
    $GLOBALS['TL_DCA']['tl_news_podcasts_feed']['fields']['news_categoriesRoot'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['news_categoriesRoot'],
        'exclude' => true,
        'inputType' => 'newsCategoriesPicker',
        'foreignKey' => 'tl_news_category.title',
        'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
        'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
    ];
}

