<?php
namespace Clickpress\NewsPodcasts\EventListener;

use Clickpress\NewsPodcasts\NewsPodcastsBackend;
use Contao\CoreBundle\ServiceAnnotation\Hook;

/**
 * @Hook("removeOldFeeds", priority=10)
 */
class RemoveOldFeedsListener
{
    public function __invoke(): array
    {
        return NewsPodcastsBackend::preservePodcastFeeds();
    }
}
