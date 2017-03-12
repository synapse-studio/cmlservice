<?php

namespace Drupal\cmlservice\Protocol;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for page example routes.
 */
class CmlQuery extends ControllerBase {

  /**
   * Main.
   */
  public static function main($type) {
    if ($type == 'sale') {
      $result  = "success\n";
    }
    else {
      $result  = "failure\n";
      $result .= "unknown type\n";
    }
    Cml::debug(__CLASS__, "sale mode query");
    return $result;
  }

}
