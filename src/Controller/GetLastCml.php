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

}
