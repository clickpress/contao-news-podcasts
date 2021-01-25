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
