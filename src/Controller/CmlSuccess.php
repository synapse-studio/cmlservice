<?php

namespace Drupal\cmlservice\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\cmlservice\Controller\Cml;

/**
 * Controller routines for page example routes.
 */
class CmlSuccess extends ControllerBase {

  public static function main($type) {
    if ($type == 'sale'){ //TODO
      $result  = "success\n";
    }else{
      $result  = "failure\n";
      $result .= "unknown type\n";
    }
    Cml::debug(__CLASS__, "sale mode query");
    return $result;
  }

}
