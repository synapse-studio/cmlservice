<?php

namespace Drupal\cmlservice\Xml;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Transliteration\PhpTransliteration;
use Symfony\Component\Yaml\Yaml;

/**
 * Tovar Parcer.
 */
class TovarParcer extends ControllerBase {

  /**
   * Parce FilePath.
   */
  public static function getRows($filepath, $skip_cache = FALSE) {
    $rows = &drupal_static("TovarParcer::getRows():$filepath");
    if (!isset($rows)) {
      $cache_key = 'TovarParcer:' . $filepath;
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
    $xmlObj->get('import', 'product');
    $products = $xmlObj->xmlfind;

    $result = [];
    if ($products) {
      foreach ($products as $products1c) {
        $key = $products1c['Ид'];
        $id = strstr("{$key}#", "#", TRUE);
        foreach ($map as $map_key => $map_info) {
          $name = $trans->transliterate($map_key, '');
          if (!isset($result[$id]['offers'][$key])) {
            $result[$id]['offers'][$key] = [];
          }
          if (isset($map_info['dst']) && $map_info['dst'] == 'offers') {
            $result[$id]['offers'][$key][$name] = $xmlObj->prepare($products1c, $map_key, $map_info);
          }
          else {
            $result[$id]['product'][$name] = $xmlObj->prepare($products1c, $map_key, $map_info);
          }
        }
      }
    }
    return $result;
  }

  /**
   * Map.
   */
  public static function map() {
    $config = \Drupal::config('cmlservice.mapsettings');
    $map_sdandart = Yaml::parse($config->get('tovar-standart'));
    $map_dop = Yaml::parse($config->get('tovar-dop'));
    if (is_array($map_dop)) {
      $map = array_merge($map_sdandart, $map_dop);
    }
    else {
      $map = $map_sdandart;
    }
    return $map;
  }

}
