<?php

namespace Drupal\cmlservice\Protocol;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for page example routes.
 */
class CmlImport extends ControllerBase {

  /**
   * Main.
   */
  public static function main() {
    Cml::debug(__FUNCTION__, "test");
    $result = __CLASS__;
    return $result;
  }

}
