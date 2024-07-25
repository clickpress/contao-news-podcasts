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
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['tl_xing_category']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
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
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['title'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'alias' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['alias'],
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
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['language'],
            'exclude' => true,
            'search' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 32, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'subtitle' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['subtitle'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'long'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'summary' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['summary'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'description' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['description'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['style' => 'height:60px', 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'category' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['category'],

            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'eval' => ['chosen' => true, 'mandatory' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'explicit' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['explicit'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => ['no', 'clean', 'yes'],
            'default' => 'no',
            'eval' => ['chosen' => true, 'includeBlankOption' => false],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'owner' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['owner'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'email' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['email'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'email', 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'author' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['author'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'image' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['image'],
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
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['useEpisodeImage'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'archives' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['archives'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'checkbox',
            'eval' => ['multiple' => true, 'mandatory' => true],
            'sql' => 'blob NULL',
        ],
        'maxItems' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['maxItems'],
            'default' => 25,
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => "smallint(5) unsigned NOT NULL default '0'",
        ],
        'feedBase' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['feedBase'],
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
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['addStatistics'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'statisticsPrefix' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['statisticsPrefix'],
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

