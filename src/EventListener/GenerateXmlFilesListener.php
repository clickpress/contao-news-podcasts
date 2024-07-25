<?php

namespace Clickpress\NewsPodcasts\EventListener;

use Clickpress\NewsPodcasts\Frontend\NewsPodcastsFrontend;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Exception;

#[AsHook('generateXmlFiles', priority: 10)]
class GenerateXmlFilesListener
{
    /**
     * @throws Exception
     */
    public function __invoke(): void
    {
        NewsPodcastsFrontend::generateFeeds();
    }
}
