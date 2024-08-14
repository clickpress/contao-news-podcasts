<?php
namespace Clickpress\NewsPodcasts\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Slug\Slug;
use Contao\DataContainer;
use Contao\StringUtil;
use http\Exception\RuntimeException;


/**
 * Check the RSS-feed alias.
 */
#[AsCallback(table: 'tl_news_podcasts_feed', target: 'fields.alias.save')]
class CheckFeedAliasCallback
{

    public function __construct(private readonly Slug $slug)
    {
    }

    public function __invoke(string $varValue, DataContainer $dc): mixed
    {
        // No change or empty value
        if ($varValue === $dc->value || '' === $varValue) {
            return $varValue;
        }

        //@ToDo: use slug generator
        $slug = $this->getSlug($varValue);

        $arrFeeds = (new \Contao\Automator())->purgeXmlFiles(true);

        // Alias exists
        if (in_array($slug, $arrFeeds, true)) {
            throw new RuntimeException(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $slug));
        }

        return $slug;
    }

    public function getSlug(string $text, string $locale = 'en', string $validChars = '0-9a-z'): string
    {
        $options = [
            'locale' => $locale,
            'validChars' => $validChars,
        ];

        return $this->slug->generate($text, $options);
    }
}
