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

    if (!is_numeric($cml_id)) {
      $cml_id = self::queryLast();
    }

    if (is_numeric($cml_id)) {
      $cml = \Drupal::entityManager()->getStorage('cml')->load($cml_id);
    }
    return $cml;
  }

  /**
   * Query.
   */
  public static function queryLast() {
    $query = \Drupal::entityQuery('cml');
    $query->condition('field_cml_xml', 'NULL', '!=')
      ->sort('field_cml_date', 'DESC')
      ->range(0, 1);
    $result = $query->execute();
    return array_shift($result);
  }
  
  /**
   * Find old catalogs.
   */
  public static function findOldCatalog() {
    $query = \Drupal::entityQuery('cml');
    $query->condition('field_cml_xml', 'NULL', '!=')
      ->condition('field_cml_status', 'done', '=')
      ->sort('field_cml_date', 'DESC');
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
      ->sort('field_cml_date', 'DESC');
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
