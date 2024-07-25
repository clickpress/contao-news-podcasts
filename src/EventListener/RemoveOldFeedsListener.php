<?php

namespace Clickpress\NewsPodcasts\EventListener;

use Clickpress\NewsPodcasts\Backend\NewsPodcastsBackend;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;

#[AsHook('removeOldFeeds')]
class RemoveOldFeedsListener
{
    public function __invoke(): array
    {
        return NewsPodcastsBackend::preservePodcastFeeds();
    }
}
