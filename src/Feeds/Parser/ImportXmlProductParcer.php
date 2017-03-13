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
use Drupal\file\Entity\File;

/**
 * Defines a CmlProductParser feed parser.
 *
 * @FeedsParser(
 *   id = "ImportXmlProductParcer",
 *   title = @Translation("CmlProduct"),
 *   description = @Translation("import.xml to products"),
 *   form = {
 *     "configuration" = "Drupal\cmlservice\Feeds\Parser\Form\ProductParcerForm",
 *     "feed" = "Drupal\cmlservice\Feeds\Parser\Form\ProductParcerFeedForm",
 *   },
 * )
 */
class ImportXmlProductParcer extends PluginBase implements ParserInterface {

  /**
   * {@inheritdoc}
   */
  public function parse(FeedInterface $feed, FetcherResultInterface $fetcher_result, StateInterface $state) {
    $result = new ParserResult();
    $trans  = new PhpTransliteration();
    $feed_config = $feed->getConfigurationFor($this);
    $offers = [];
    if ($feed_config['offers']) {
      $offers = $this->queryOffers();
      dsm($offers);
    }
    $images = [];
    if ($feed_config['images']) {
      $images = $this->queryImages();
      dsm($images);
    }
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
        $image = NULL;
        if (isset($raw['Kartinka']) && isset($images[$raw['Kartinka']])) {
          $image = $images[$raw['Kartinka']];
        }
        $item->set('image', $image);
        $result->addItem($item);
      }
    }

    dsm($result);
    return $result;
  }

  /**
   * Find Images.
   */
  public function queryImages() {
    $query = \Drupal::entityQuery('file');
    $query->condition('uri', '%import_files%', 'LIKE');
    $result = $query->execute();
    $files = [];
    foreach ($result as $fid) {
      $file = File::load($fid);
      $file->setPermanent();
      $file->save();
      $uri = $file->getFileUri();
      // Hack: /var/www/html/modules/contrib/feeds/src/Feeds/Target/Link.php
      // case 'target_id': $values[$column] = (int) $value; break;!
      $image = strstr($uri, 'import_files');
      $files[$image] = ['target_id' => $fid];
    }
    return $files;
  }

  /**
   * Find Offers.
   */
  public function queryOffers() {
    $query = \Drupal::entityQuery('cml');
    $query->condition('field_cml_xml', 'NULL', '!=')
      ->sort('field_cml_date', 'DESC')
      ->range(0, 1);
    $result = $query->execute();
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
    $result['image'] = ['label' => 'Image from Drupal'];
    $result['offers'] = ['label' => 'Offers from Drupal'];
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultFeedConfiguration() {
    return [
      'offers' => $this->configuration['offers'],
      'images' => $this->configuration['images'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'offers' => FALSE,
      'images' => FALSE,
    ];
  }

}
