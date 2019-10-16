<?php

/*
 * This file is part of NewsPodcasts.
 *
 * (c) Stefan Schulz-Lauterbach <ssl@clickpress.de>
 *
 * @license LGPL-3.0-or-later
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
