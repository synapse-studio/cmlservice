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
    $import_files_url = file_create_url('public://cml-files/img/');
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
        $id = $products1c['Ид'];
        if (strripos($id, '#') === FALSE || !$config->get('hash-skip')) {
          $product = [];
          foreach ($map as $map_key => $map_info) {
            $name = $trans->transliterate($map_key, '');
            $product[$name] = $xmlObj->prepare($products1c, $map_key, $map_info);
          }
          $result[$id] = $product;
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
