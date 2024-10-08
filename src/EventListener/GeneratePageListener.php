<?php

namespace Clickpress\NewsPodcasts\EventListener;

use Clickpress\NewsPodcasts\Model\NewsPodcastsFeedModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Environment;
use Contao\FragmentTemplate;
use Contao\LayoutModel;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\StringUtil;

/**
 * @internal
 */
class GeneratePageListener
{
    private ContaoFramework $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Adds the feeds to the page header.
     */
    public function __invoke(PageModel $pageModel, LayoutModel $layoutModel): void
    {
        $podcastfeeds = StringUtil::deserialize($layoutModel->podcastfeeds);

        if (empty($podcastfeeds) || !\is_array($podcastfeeds)) {
            return;
        }

        $this->framework->initialize();

        $adapter = $this->framework->getAdapter(NewsPodcastsFeedModel::class);

        if (!($feeds = $adapter::findByIds($podcastfeeds)) instanceof Collection) {
            return;
        }

        $template = $this->framework->getAdapter(FragmentTemplate::class);

        $environment = $this->framework->getAdapter(Environment::class);

        foreach ($feeds as $feed) {
            $GLOBALS['TL_HEAD'][] = $template::generateFeedTag(
                sprintf('%sshare/%s.xml', $feed->feedBase ?: $environment::get('base'), $feed->alias),
                $feed->format,
                $feed->title
            );
        }
    }
}
