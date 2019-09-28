<?php

/*
 * This file is part of NewsPodcasts.
 *
 * (c) Stefan Schulz-Lauterbach
 *
 * @license LGPL-3.0-or-later
 */


/**
 * Table tl_news_podcasts_feed
 */
$GLOBALS['TL_DCA']['tl_news_podcasts_feed'] = array
(

    // Config
    'config'   => array
    (
        'dataContainer'     => 'Table',
        'enableVersioning'  => true,
        'onload_callback'   => array
        (
            array( 'Clickpress\\NewsPodcasts\\NewsPodcastsBackend', 'checkPermission' ),
            //array(  'Clickpress\\NewsPodcasts\\NewsPodcastsBackend', 'generateFeed' )
        ),
        'onsubmit_callback' => array
        (
            array( 'Clickpress\\NewsPodcasts\\NewsPodcastsBackend', 'scheduleUpdate' )
        ),
        'sql'               => array
        (
            'keys' => array
            (
                'id'    => 'primary',
                'alias' => 'index'
            )
        ),
        'backlink'          => 'do=news'
    ),

    // List
    'list'     => array
    (
        'sorting'           => array
        (
            'mode'        => 1,
            'fields'      => array( 'title' ),
            'flag'        => 1,
            'panelLayout' => 'filter;search,limit'
        ),
        'label'             => array
        (
            'fields' => array( 'title' ),
            'format' => '%s'
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ),
        ),
        'operations'        => array
        (
            'edit'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ),
            'copy'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif'
            ),
            'delete' => array
            (
                'label'      => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default' => '{title_legend},title,alias,language;{description_legend},subtitle,description,category,explicit;{author_legend},author,owner,email,image,copyright;{archives_legend},archives;{config_legend},maxItems,feedBase;{statistic_legend},addStatistics;',
        '__selector__' => array('addStatistics')
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'addStatistics' => 'statisticsPrefix'
    ),

    // Fields
    'fields'   => array
    (
        'id'          => array
        (
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp'      => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'title'       => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['title'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => array( 'mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50' ),
            'sql'       => "varchar(255) NOT NULL default ''"
        ),
        'alias'       => array
        (
            'label'         => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['alias'],
            'exclude'       => true,
            'search'        => true,
            'inputType'     => 'text',
            'eval'          => array(
                'mandatory' => true,
                'rgxp'      => 'alias',
                'unique'    => true,
                'maxlength' => 128,
                'tl_class'  => 'w50'
            ),
            'save_callback' => array
            (
                array( 'Clickpress\\NewsPodcasts\\NewsPodcastsBackend', 'checkFeedAlias' )
            ),
            'sql'           => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
        ),
        'language'    => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['language'],
            'exclude'   => true,
            'search'    => true,
            'filter'    => true,
            'inputType' => 'text',
            'eval'      => array( 'mandatory' => true, 'maxlength' => 32, 'tl_class' => 'w50' ),
            'sql'       => "varchar(32) NOT NULL default ''"
        ),
        'subtitle'    => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['subtitle'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => array( 'mandatory' => true, 'maxlength' => 255, 'tl_class' => 'long' ),
            'sql'       => "varchar(255) NOT NULL default ''"
        ),
        'summary'     => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['summary'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'textarea',
            'eval'      => array( 'rte' => 'tinyMCE', 'tl_class' => 'clr' ),
            'sql'       => "text NULL"
        ),
        'description' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['description'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'textarea',
            'eval'      => array( 'style' => 'height:60px', 'tl_class' => 'clr' ),
            'sql'       => "text NULL"
        ),
        'category'    => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['category'],

            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => array( 'Clickpress\\NewsPodcasts\\NewsPodcastsBackend', 'getItunesCategories' ),
            'eval'             => array( 'chosen' => true, 'mandatory' => true ),
            'sql'              => "varchar(255) NOT NULL default ''"
        ),
        'explicit'    => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['explicit'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => array( 'no', 'clean', 'yes' ),
            'default'   => 'no',
            'eval'      => array( 'chosen' => true, 'includeBlankOption' => false ),
            'sql'       => "varchar(255) NOT NULL default ''"
        ),
        'owner'       => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['owner'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array( 'mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50' ),
            'sql'       => "varchar(255) NOT NULL default ''"
        ),
        'email'       => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['email'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array( 'mandatory' => true, 'rgxp' => 'email', 'maxlength' => 255, 'tl_class' => 'w50' ),
            'sql'       => "varchar(255) NOT NULL default ''"
        ),
        'author'      => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['author'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array( 'mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50' ),
            'sql'       => "varchar(255) NOT NULL default ''"
        ),
        'image'       => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['image'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => array(
                'fieldType'  => 'radio',
                'filesOnly'  => true,
                'extensions' => 'jpg,png',
                'mandatory'  => true,
                'tl_class' => 'long clr'
            ),
            'sql'       => "blob NULL"
        ),

        'archives' => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['archives'],
            'exclude'          => true,
            'search'           => true,
            'inputType'        => 'checkbox',
            'options_callback' => array( 'Clickpress\\NewsPodcasts\\NewsPodcastsBackend', 'getAllowedArchives' ),
            'eval'             => array( 'multiple' => true, 'mandatory' => true ),
            'sql'              => "blob NULL"
        ),
        'maxItems' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['maxItems'],
            'default'   => 25,
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array( 'mandatory' => true, 'rgxp' => 'natural', 'tl_class' => 'w50' ),
            'sql'       => "smallint(5) unsigned NOT NULL default '0'"
        ),
        'feedBase' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['feedBase'],
            'default'   => \Contao\Environment::get( 'base' ),
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => array( 'trailingSlash'  => true,
                                  'rgxp'           => 'url',
                                  'decodeEntities' => true,
                                  'maxlength'      => 255,
                                  'tl_class'       => 'w50'
            ),
            'sql'       => "varchar(255) NOT NULL default ''"
        ),
        'addStatistics' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['addStatistics'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array('submitOnChange' => true),
            'sql'       => "char(1) NOT NULL default ''",
        ),
        'statisticsPrefix' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_news_podcasts_feed']['statisticsPrefix'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'default'   => '',
            'eval'      => array( 'trailingSlash'  => true,
                                  'rgxp'           => 'url',
                                  'decodeEntities' => true,
                                  'maxlength'      => 255,
                                  'tl_class'       => 'w50'
            ),
            'sql'       => "varchar(255) NOT NULL default ''"
        ),
    )
);
