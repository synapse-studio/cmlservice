<?php

namespace Drupal\cmlservice\Feeds\Parser;

/**
 * @file
 * Contains \Drupal\cmlservice\Feeds\Parser\OffersXmlProductVariationsParcer.
 */

use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Item\DynamicItem;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Plugin\Type\Parser\ParserInterface;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\Result\ParserResult;
use Drupal\feeds\StateInterface;
use Drupal\Component\Transliteration\PhpTransliteration;
use Drupal\cmlservice\Xml\OffersParcer;

/**
 * Defines a CmlOffersParser feed parser.
 *
 * @FeedsParser(
 *   id = "OffersXmlProductVariationsParcer",
 *   title = @Translation("CmlProductVariations"),
 *   description = @Translation("offers.xml to product variations"),
 *   form = {
 *     "configuration" = "Drupal\cmlservice\Feeds\Parser\Form\VariationsParcerForm",
 *   },
 * )
 */
class OffersXmlProductVariationsParcer extends PluginBase implements ParserInterface {

  /**
   * {@inheritdoc}
   */
  public function parse(FeedInterface $feed, FetcherResultInterface $fetcher_result, StateInterface $state) {
    $result = new ParserResult();
    $feed_config = $feed->getConfigurationFor($this);
    $trans  = new PhpTransliteration();
    $xml = $fetcher_result->getRaw();
    $raws = OffersParcer::parce($xml);
    $map = OffersParcer::map();

    if ($feed_config['limit']) {
      $raws = array_slice($raws, 0, $feed_config['limit']);
      dsm($raws);
    }
    if ($raws) {
      foreach ($raws as $raw) {
        $item = new DynamicItem();
        foreach ($map as $map_key => $map_info) {
          $name = $trans->transliterate($map_key, '');
          $item->set($name, $raw[$name]);
        }
        $result->addItem($item);
      }
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingSources() {
    $map = OffersParcer::map();
    $trans = new PhpTransliteration();
    $result = [];
    foreach ($map as $map_key => $map_info) {
      $name = $trans->transliterate($map_key, '');
      if (isset($map_info['type']) && is_array($map_info['type'])) {
        $map_key .= ' []';
      }
      $result[$name] = ['label' => $map_key];
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultFeedConfiguration() {
    return [
      'limit' => $this->configuration['limit'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'limit' => '',
    ];
  }

}
