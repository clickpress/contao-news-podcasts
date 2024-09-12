<?php
namespace Clickpress\NewsPodcasts\EventListener\DataContainer;

use Clickpress\NewsPodcasts\Frontend\NewsPodcastsFrontend;
use Contao\Automator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use http\Exception\RuntimeException;


/**
 * Check the RSS-feed alias.
 */
#[AsCallback(table: 'tl_news_podcasts_feed', target: 'fields.alias.save')]
readonly class CheckFeedAliasCallback
{
    public function __invoke(string $varValue, DataContainer $dc): mixed
    {
        // No change or empty value
        if ($varValue === $dc->value || '' === $varValue) {
            return $varValue;
        }

        $slug = (new NewsPodcastsFrontend)->getSlug($varValue);

        $arrFeeds = (new Automator())->purgeXmlFiles(true);

        // Alias exists
        if (in_array($slug, $arrFeeds, true)) {
            throw new RuntimeException(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $slug));
        }

        return $slug;
    }
}
