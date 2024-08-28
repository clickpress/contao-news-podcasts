<?php
namespace Clickpress\NewsPodcasts\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Symfony\Component\Yaml\Yaml;


#[AsCallback(table: 'tl_news_podcasts_feed', target: 'fields.category.options')]
class GetPodcastCategoriesCallback
{
    private array $arrItunesCategories;

    public function __invoke(): array
    {
        $categories = Yaml::parseFile(__DIR__ . '/../../../config/podcast_categories_list.yaml');

        foreach ($categories as $v) {
            $this->arrItunesCategories[$v['category']] = $v['category'];
            if (is_array($v['subcategories'])) {
                foreach ($v['subcategories'] as $sub) {
                    $this->arrItunesCategories[$v['category'] . '|' . $sub] = $v['category'] . '|' . $sub;
                }
            }
        }

        return $this->arrItunesCategories;
    }
}
