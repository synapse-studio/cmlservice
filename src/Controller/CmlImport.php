<?php

namespace Drupal\cmlservice\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for page example routes.
 */
class CmlImport extends ControllerBase {

  public static function main() {
    self::debug(__FUNCTION__, "test");
    $result = __CLASS__;
    return $result;
  }

  public static function debug($function, $message) {
    $debug = false;
    $config = \Drupal::config('cmlservice.settings');
    if ($config->get('debug')){
      \Drupal::logger($function)->notice($message);
      $debug = true;
    }
    return $debug;
  }

}
