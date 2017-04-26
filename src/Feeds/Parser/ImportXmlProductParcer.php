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

    $xml = $fetcher_result->getRaw();
    $raws = TovarParcer::parce($xml);
    $map = TovarParcer::map();

    if ($feed_config['limit']) {
      $raws = array_slice($raws, 0, $feed_config['limit']);
      dsm($raws);
    }
    
    if (isset($feed->field_feeds_import_restrict->value) && isset($feed->field_feeds_import_offset->value)) {
      $restrict = $feed->field_feeds_import_restrict->value;
      $offset = $feed->field_feeds_import_offset->value;
      $countRaws = count($raws);
      if (($countRaws > $restrict) && ($offset < $countRaws)) {
        $raws = array_slice($raws, $offset, $restrict);
        $feed->field_feeds_import_offset = $offset + $restrict;
        $feed->save();
      }
      elseif ($offset != 0) {
        $feed->field_feeds_import_offset = 0;
        $feed->save();
        $raws = [];
      }
    }

    if ($raws) {
      foreach ($raws as $raw) {
        $item = new DynamicItem();
        foreach ($map as $map_key => $map_info) {
          $name = $trans->transliterate($map_key, '');
          $item->set($name, $raw[$name]);
        }
        $item->set('image', $this->selectImage($raw));
        $item->set('offers', $this->selectOffer($raw));
        $result->addItem($item);
      }
    }

    return $result;
  }

  /**
   * HasImage.
   */
  public function hasOffer($raw, $offers) {
    $offer = [];
    if (!empty($offers) && isset($raw['Id'])) {
      $id1c = $raw['Id'];
      if (isset($offers[$id1c])) {
        $offer = $offers[$id1c];
      }
    }
    return $offer;
  }
  
  /**
   * Find offer in table.
   */
  public function selectOffer($raw) {
    $result = [];
    if (isset($raw['Id'])) {
      $id1c = $raw['Id'];
      $entity_type = 'commerce_product_variation';
      $query = \Drupal::entityQuery($entity_type);
      $query->condition('sku', $id1c . '%', 'LIKE');
      $ids = $query->execute();
      $offers = entity_load_multiple($entity_type, $ids);
      foreach ($offers as $offer) {
        $sku = $offer->sku->value;
        if ($sku && strlen($sku) > 20) {
          $result[] = $sku;
        }
      }
    }
    return $result;
  }

  /**
   * HasImage.
   */
  public function hasImage($raw, $images) {
    $image = [];
    if (!empty($images) && isset($raw['Kartinka'])) {
      $url1c = $raw['Kartinka'];
      if (isset($images[$url1c])) {
        $image = $images[$url1c];
      }
    }
    return $image;
  }
  
  /**
   * Find image in table.
   */
  public function selectImage($raw) {
    $result = [];
    if (isset($raw['Kartinka'])) {
      $query = \Drupal::entityQuery('file');
      $query->condition('uri', '%' . $raw['Kartinka'] . '%', 'LIKE');
      $query->sort('created', 'DESC');
      $fleIds = $query->execute();
      $fid = array_shift($fleIds);
      $result['id'] = $fid;
    }
    return $result;
  }

  /**
   * Find Images.
   */
  public function queryImages($flag) {
    $files = [];
    if ($flag) {
      $query = \Drupal::entityQuery('file');
      $query->condition('uri', '%import_files%', 'LIKE');
      $result = $query->execute();
      foreach ($result as $fid) {
        $file = File::load($fid);
        $file->setPermanent();
        $file->save();
        $uri = $file->getFileUri();
        // Hack: /var/www/html/modules/contrib/feeds/src/Feeds/Target/Link.php
        // case 'target_id': $values[$column] = (int) $value; break;!
        $image = strstr($uri, 'import_files');
        $files[$image] = ['id' => $fid];
      }
    }
    return $files;
  }

  /**
   * Find Offers.
   */
  public function queryOffers($flag) {
    $result = [];
    if ($flag) {
      $entity_type = 'commerce_product_variation';
      $query = \Drupal::entityQuery($entity_type);
      $ids = $query->execute();
      $offers = entity_load_multiple($entity_type, $ids);

      foreach ($offers as $offer) {
        $id = $offer->id();
        $sku = $offer->sku->value;
        if ($sku && strlen($sku) > 20) {
          $key = strstr($sku . "#", "#", TRUE);
          $result[$key][] = $sku;
        }
      }
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
      'limit'  => $this->configuration['limit'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'offers' => FALSE,
      'images' => FALSE,
      'limit' => FALSE,
    ];
  }

}
