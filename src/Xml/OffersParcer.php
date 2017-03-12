<?php

namespace Drupal\cmlservice\Xml;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\Yaml\Yaml;
use Drupal\Component\Transliteration\PhpTransliteration;

/**
 * Controller routines for page example routes.
 */
class OffersParcer extends ControllerBase {

  /**
   * Parce.
   */
  public static function parce($xml, $uri = FALSE) {
    $config = \Drupal::config('cmlservice.mapsettings');
    $trans = new PhpTransliteration();
    $map = self::map();

    $xmlObj = new XmlObject();
    $xmlObj->parseXmlString($xml);
    $xmlObj->get('offers', 'offer');
    $offers = $xmlObj->xmlfind;

    $result = [];
    if ($offers) {
      if ($config->get('hash-skip') && $uri) {
        $filepath = drupal_realpath(str_replace('offers', 'import', $uri));
        $xmlObj2 = new XmlObject();
        $xmlObj2->parseXmlFile($filepath);
        $xmlObj2->parseXmlString($xmlObj2->xmlString);
        $xmlObj2->get('import', 'product');
        $products = [];
        foreach ($xmlObj2->xmlfind as $key => $product) {
          $id = $product['Ид'];
          $key = 'ХарактеристикиТовара';
          $char = $xmlObj2->prepare($product, $key, ['type' => []]);
          if ($char) {
            $products[$id] = $char;
          }
        }
      }
      foreach ($offers as $offer1c) {
        $id = $offer1c['Ид'];
        $parent = strstr($id, "#", TRUE);
        if ($config->get('hash-skip') && $uri) {
          if (isset($products[$id]) && $products[$id]) {
            $offer1c['Характеристики'] = $products[$id];
          }
        }
        if (isset($result[$parent])) {
          //unset($result[$parent]);
        }
        $offer = [];
        foreach ($map as $map_key => $map_info) {
          $name = $trans->transliterate($map_key, '');
          $offer[$name] = $xmlObj->prepare($offer1c, $map_key, $map_info);
        }
        $result[$id] = $offer;

      }
    }

    return $result;
  }

  /**
   * Map.
   */
  public static function map() {
    $config = \Drupal::config('cmlservice.mapsettings');
    $map_sdandart = Yaml::parse($config->get('offers-standart'));
    $map_dop = Yaml::parse($config->get('offers-dop'));
    if (is_array($map_dop)) {
      $map = array_merge($map_sdandart, $map_dop);
    }
    else {
      $map = $map_sdandart;
    }
    return $map;
  }

}
