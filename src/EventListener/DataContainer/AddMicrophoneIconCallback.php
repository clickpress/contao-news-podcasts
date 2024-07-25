<?php

namespace Clickpress\NewsPodcasts\EventListener\DataContainer;

use Contao\Config;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Date;

#[AsCallback(table: 'tl_news', target: 'list.sorting.child_record')]
class AddMicrophoneIconCallback
{
    public function __invoke(array $arrRow): string
    {
        $strHtml = $arrRow['headline'] .
            ' <img src="bundles/newspodcasts/icon_mic.svg" width="16" height="16" alt="Podcast">';
        $arrRow['headline'] = ('1' === $arrRow['addPodcast']) ? $strHtml : $arrRow['headline'];

        return $this->listNewsArticles($arrRow);
    }

    private function listNewsArticles(array $arrRow): string
    {
        $date = Date::parse(Config::get('datimFormat'), $arrRow['date']);

        return '<div class="tl_content_left">'
            . $arrRow['headline']
            . ' <span style="color:#999;padding-left:3px">[' . $date . ']</span></div>';
    }

}
