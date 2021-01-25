<?php

/*
 * This file is part of NewsPodcasts.
 *
 * (c) Stefan Schulz-Lauterbach <ssl@clickpress.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clickpress\NewsPodcasts\Tests;

use Clickpress\NewsPodcasts\NewsPodcastsBundle;
use PHPUnit\Framework\TestCase;

class ClickpressNewsPodcastsTest extends TestCase
{
    public function testCanBeInstantiated()
    {
        $bundle = new NewsPodcastsBundle();

        $this->assertInstanceOf('Clickpress\NewsPodcasts\NewsPodcastsBundle', $bundle);
    }
}
