<?php

namespace Clickpress\NewsPodcasts\EventListener;

use Clickpress\NewsPodcasts\Model\NewsPodcastsFeedModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Environment;
use Contao\LayoutModel;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;

/**
 * @internal
 */
class GeneratePageListener
{
    /**
     * @var ContaoFramework
     */
    private $framework;

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

        /** @var NewsPodcastsFeedModel $adapter */
        $adapter = $this->framework->getAdapter(NewsPodcastsFeedModel::class);

        if (!($feeds = $adapter->findByIds($podcastfeeds)) instanceof Collection) {
            return;
        }

        /** @var Template $template */
        $template = $this->framework->getAdapter(Template::class);

        /** @var Environment $environment */
        $environment = $this->framework->getAdapter(Environment::class);

        foreach ($feeds as $feed) {
            $GLOBALS['TL_HEAD'][] = $template->generateFeedTag(
                sprintf('%sshare/%s.xml', $feed->feedBase ?: $environment->get('base'), $feed->alias),
                $feed->format,
                $feed->title
            );
        }
    }
}
