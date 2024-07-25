<?php
namespace Clickpress\NewsPodcasts\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\StringUtil;
use http\Exception\RuntimeException;


/**
 * Check the RSS-feed alias.
 */
#[AsCallback(table: 'tl_news_podcasts_feed', target: 'fields.alias.save')]
class CheckFeedAliasCallback
{

    public function __invoke(string $varValue, DataContainer $dc): mixed
    {
        // No change or empty value
        if ($varValue === $dc->value || '' === $varValue) {
            return $varValue;
        }

        //@ToDo: use slug generator
        $varValue = StringUtil::standardize($varValue); // see #5096

        //@ToDo: do not import the automator
        $this->import('Automator');

        $arrFeeds = $this->Automator->purgeXmlFiles(true);

        // Alias exists
        if (in_array($varValue, $arrFeeds, true)) {
            throw new RuntimeException(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        return $varValue;
    }
}
