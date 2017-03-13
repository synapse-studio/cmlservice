<?php

namespace Drupal\cmlservice\Feeds\Parser;

/**
 * @file
 * Contains \Drupal\cmlservice\Feeds\Parser\ImportXmlProductParcer.
 */

use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Item\DynamicItem;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Plugin\Type\Parser\ParserInterface;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\Result\ParserResult;
use Drupal\feeds\StateInterface;
use Drupal\Component\Transliteration\PhpTransliteration;
use Drupal\cmlservice\Xml\TovarParcer;

/**
 * Defines a CmlProductParser feed parser.
 *
 * @FeedsParser(
 *   id = "ImportXmlProductParcer",
 *   title = @Translation("CmlProduct"),
 *   description = @Translation("import.xml to products")
 * )
 */
class ImportXmlProductParcer extends PluginBase implements ParserInterface {

  /**
   * {@inheritdoc}
   */
  public function parse(FeedInterface $feed, FetcherResultInterface $fetcher_result, StateInterface $state) {
    $result = new ParserResult();
    $trans  = new PhpTransliteration();
    $xml = $fetcher_result->getRaw();
    $raws = TovarParcer::parce($xml);
    $map = TovarParcer::map();
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

    if (FALSE) {
      dsm($result);
      $result = new ParserResult();
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingSources() {
    $map = TovarParcer::map();
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

}
