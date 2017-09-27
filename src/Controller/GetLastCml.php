<?php

namespace Drupal\cmlservice\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * GetLast.
 */
class GetLastCml extends ControllerBase {

  /**
   * Load.
   */
  public static function load($cml_id = FALSE) {
    $cml = FALSE;

    // 1C Importing in proccess.
    $proccess = \Drupal::config('cmlservice.settings')->get('current-import');
    if ($proccess) {
      $cml_id = (int) $proccess;
    }

    if (!is_numeric($cml_id)) {
      $cml_id = self::queryLast();
    }

    if (is_numeric($cml_id)) {
      $cml = \Drupal::entityManager()->getStorage('cml')->load($cml_id);
    }
    return $cml;
  }

  /**
   * LastFilePath.
   */
  public static function filePath($xmlkey = 'import', $cml_id = FALSE) {
    $cml = self::load($cml_id);
    if (is_object($cml)) {
      $cml_id = $cml->id();
    }
    $filepath = &drupal_static("GetLastCml::filePath():$xmlkey:$cml_id");
    if (!isset($filepath)) {
      $cache_key = "GetLastCml-$xmlkey:$cml_id";
      if ($cache = \Drupal::cache()->get($cache_key)) {
        $filepath = $cache->data;
      }
      else {
        if (is_object($cml)) {
          $cml_xml = $cml->field_cml_xml->getValue();
          $files = [];
          $data = FALSE;
          $filekeys[$xmlkey] = TRUE;
          if (!empty($cml_xml)) {
            foreach ($cml_xml as $xml) {
              $file = file_load($xml['target_id']);
              $filename = $file->getFilename();
              $filekey = strstr($filename, '.', TRUE);
              if (isset($filekeys[$filekey]) && $filekeys[$filekey]) {
                $files[] = $file->getFileUri();
              }
            }
          }
          $filepath = array_shift($files);
          \Drupal::cache()->set($cache_key, $filepath);
        }
      }
    }
    return $filepath;
  }

  /**
   * Query.
   */
  public static function queryLast() {
    $query = \Drupal::entityQuery('cml');
    $query->condition('field_cml_xml', 'NULL', '!=')
      ->condition('field_cml_status', 'done', '!=')
      ->sort('created', 'ASC')
      ->range(0, 1);
    $result = $query->execute();
    if (!count($result)) {
      $query = \Drupal::entityQuery('cml');
      $query->condition('field_cml_xml', 'NULL', '!=')
        ->sort('created', 'DESC')
        ->range(0, 1);
      $result = $query->execute();
    }
    return array_shift($result);
  }

  /**
   * Find old catalogs.
   */
  public static function findOldCatalog() {
    $query = \Drupal::entityQuery('cml');
    $query->condition('field_cml_xml', 'NULL', '!=')
      ->condition('field_cml_status', 'done', '=')
      ->sort('created', 'DESC');
    $result = $query->execute();
    $operations = [];
    if (count($result) > 3) {
      $result = array_slice($result, 3);
      foreach ($result as $cmlId) {
        $operations[] = [
          'cmlservice_delete_old_cml',
          [
            $cmlId,
          ],
        ];
      }
    }
    return $operations;
  }

  /**
   * Find old catalogs.
   */
  public static function findEmptyCml() {
    $query = \Drupal::entityQuery('cml');
    $query->notExists('field_cml_xml')
      ->sort('created', 'DESC');
    $result = $query->execute();
    $operations = [];
    foreach ($result as $cmlId) {
      $operations[] = [
        'cmlservice_delete_old_cml',
        [
          $cmlId,
        ],
      ];
    }
    return $operations;
  }

}
