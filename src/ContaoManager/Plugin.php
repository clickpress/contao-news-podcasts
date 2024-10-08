<?php

namespace Clickpress\NewsPodcasts\ContaoManager;

use Clickpress\NewsPodcasts\NewsPodcastsBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(NewsPodcastsBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
