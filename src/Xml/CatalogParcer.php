<?php

namespace Drupal\cmlservice\Xml;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for page example routes.
 */
class CatalogParcer extends ControllerBase {

  /**
   * Parce FilePath.
   */
  public static function getRows($filepath, $skip_cache = FALSE) {
    $rows = &drupal_static("CatalogParcer::getRows():$filepath");
    if (!isset($rows)) {
      $cache_key = 'CatalogParcer:' . $filepath;
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
  public static function parce($xml, $flatTree = TRUE) {
    $xmlObj = new XmlObject();
    $xmlObj->parseXmlString($xml);
    $xmlObj->get('import', 'category');
    if ($flatTree) {
      return self::flatTree($xmlObj->xmlfind);
    }
    else {
      return $xmlObj->xmlfind;
    }
  }

  /**
   * Catalog flatTree.
   */
  public static function flatTree(array $data, $parentId = NULL, $parent = TRUE) {
    $result = [];
    $i = 0;
    if (!empty($data)) {
      $data = XmlObject::arrayNormalize($data);
      foreach ($data as $key => $val) {
        $i++;
        $id = $val['Ид'];
        $result[$id]['id'] = $val['Ид'];
        $result[$id]['name'] = $val['Наименование'];
        if ($parentId) {
          $result[$id]['parent'] = $parentId && !$parent ? $parentId : FALSE;
        }
        $result[$id]['term_weight'] = $i;
        if (!empty($val['Группы']['Группа'])) {
          $result = array_merge($result, self::flatTree($val['Группы']['Группа'], $id, FALSE));
        }
      }
    }
    return $result;
  }

}
