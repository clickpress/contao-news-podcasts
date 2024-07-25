<?php
namespace Clickpress\NewsPodcasts\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Input;
use Contao\System;

/**
 * Schedule a podcast feed update.
 *
 * This method is triggered when a single news item or multiple news
 * items are modified (edit/editAll), moved (cut/cutAll) or deleted
 * (delete/deleteAll). Since duplicated items are unpublished by default,
 * it is not necessary to schedule updates on copyAll as well.
 */

#[AsCallback(table: 'tl_news', target: 'config.oncut')]
#[AsCallback(table: 'tl_news', target: 'config.onsubmit')]
#[AsCallback(table: 'tl_news', target: 'config.ondelete')]
#[AsCallback(table: 'tl_news_podcasts_feed', target: 'config.onsubmit')]
class ScheduleFeedUpdateListener
{

    public function __invoke(DataContainer $dc): void
    {
        // Return if there is no ID
        if (!$dc->activeRecord || !$dc->activeRecord->id || 'copy' === Input::get('act')) {
            return;
        }

        // Store the ID in the session
        $objSession = System::getContainer()->get('request_stack')->getSession();
        $session = $objSession->get('podcasts_feed_updater');
        $session[] = $dc->activeRecord->id;
        $objSession->set('podcasts_feed_updater', array_unique($session));
    }
}
