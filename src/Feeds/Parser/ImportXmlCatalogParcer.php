<?php

namespace Drupal\cmlservice\Feeds\Parser;

/**
 * @file
 * Contains \Drupal\cmlservice\Feeds\Parser\ImportXmlCatalogParcer.
 */

use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Item\DynamicItem;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Plugin\Type\Parser\ParserInterface;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\Result\ParserResult;
use Drupal\feeds\StateInterface;
use Drupal\cmlservice\Xml\CatalogParcer;

/**
 * Defines a CmlCatalogParent feed parser.
 *
 * @FeedsParser(
 *   id = "ImportXmlCatalogParcer",
 *   title = @Translation("CmlCatalog"),
 *   description = @Translation("import.xml to catalog tree")
 * )
 */
class ImportXmlCatalogParcer extends PluginBase implements ParserInterface {

  /**
   * {@inheritdoc}
   */
  public function parse(FeedInterface $feed, FetcherResultInterface $fetcher_result, StateInterface $state) {
    // Set time zone to GMT for parsing dates with strtotime().
    $result = new ParserResult();
    $xml = $fetcher_result->getRaw();
    $raws  = CatalogParcer::parce($xml);
    foreach ($raws as $key => $raw) {
      $item = new DynamicItem();
      $item->set('uuid', $raw['id']);
      $item->set('name', $raw['name']);
      $item->set('parent_uid', [trim($raw['parent'])]);
      $item->set('term_weight', $raw['term_weight']);
      $result->addItem($item);
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingSources() {
    return [
      'uuid' => ['label' => $this->t('uuid')],
      'name' => ['label' => $this->t('Name')],
      'term_weight' => ['label' => $this->t('Term weight')],
      'parent_uid' => ['label' => $this->t('Parent_uid')],
    ];
  }

}
