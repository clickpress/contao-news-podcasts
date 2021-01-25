<?php
namespace Clickpress\NewsPodcasts\EventListener;

use Clickpress\NewsPodcasts\NewsPodcasts;
use Contao\CoreBundle\ServiceAnnotation\Hook;

/**
 * @Hook("generateXmlFiles", priority=10)
 */
class GenerateXmlFilesListener
{
    public function __invoke(): void
    {
        NewsPodcasts::generateFeeds();

    }
}
