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
   * Parce FilePath.
   */
  public static function getRows($filepath, $skip_cache = FALSE) {
    $rows = &drupal_static("OffersParcer::getRows():$filepath");
    if (!isset($rows)) {
      $cache_key = 'OffersParcer:' . $filepath;
      if ($skip_cache) {
        $cache_key .= rand();
      }
      if ($cache = \Drupal::cache()->get($cache_key)) {
        $rows = $cache->data;
      }
      else {
        if ($filepath) {
          $xmlObj = new XmlObject();
          $xmlObj->parseXmlFile($filepath);
          $data = self::parce($xmlObj->xmlString);
          if (!empty($data)) {
            $rows = $data;
          }
        }
        \Drupal::cache()->set($cache_key, $rows);
      }
    }
    return $rows;
  }

  /**
   * Parce.
   */
  public static function parce($xml) {
    $config = \Drupal::config('cmlservice.mapsettings');
    $trans = new PhpTransliteration();
    $map = self::map();

    $xmlObj = new XmlObject();
    $xmlObj->parseXmlString($xml);
    $xmlObj->get('offers', 'offer');
    $offers = $xmlObj->xmlfind;

    $result = [];
    if ($offers) {
      foreach ($offers as $offer1c) {
        $offer = [];
        foreach ($map as $map_key => $map_info) {
          $name = $trans->transliterate($map_key, '');
          $offer[$name] = $xmlObj->prepare($offer1c, $map_key, $map_info);
        }
        $id = $offer1c['Ид'];
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
