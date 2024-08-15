<?php
namespace Clickpress\NewsPodcasts\EventListener;

use Clickpress\NewsPodcasts\Frontend\NewsPodcastsFrontend;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCallback(table: 'tl_news', target: 'config.onload')]
#[AsCallback(table: 'tl_news', target: 'config.oncut')]
#[AsCallback(table: 'tl_news', target: 'config.ondelete')]
#[AsCallback(table: 'tl_news', target: 'config.onsubmit')]
#[AsCallback(table: 'tl_news_podcasts_feed', target: 'config.onload')]
#[AsCallback(table: 'tl_news_podcasts_feed', target: 'config.onsubmit')]
class GeneratePodcastFeed
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function __invoke(): void
    {
        $session = $this->requestStack->getSession();
        $feedUpdater = $session->get('podcasts_feed_updater');

        if (!is_array($feedUpdater) || empty($feedUpdater)) {
            return;
        }

        NewsPodcastsFrontend::generateFeeds();


        $session->set('podcasts_feed_updater', null);
    }
}
