<?php

/*
 * This file is part of NewsPodcasts.
 *
 * (c) Stefan Schulz-Lauterbach <ssl@clickpress.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
