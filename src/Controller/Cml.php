<?php

namespace Drupal\cmlservice\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for page example routes.
 */
class Cml extends ControllerBase {

  public static function cml() {
    self::debug(__FUNCTION__, "test");


    $get = print_r($_GET, true);
    $post = print_r($_POST, true);
    //_cmlservice_debug(__FUNCTION__,
    // t("get&post :\nget = @get\npost = @post", array('@get'=>$get, '@post'=> $post)));

    $type = '';
    $mode = '';
    if (isset($_GET['type']) and isset($_GET['mode'])) {
      $type = $_GET['type'];
      $mode = $_GET['mode'];
    }
    self::debug(__FUNCTION__, t("q: type=@type|mode=@mode", array('@type'=>$type, '@mode'=> $mode)));

    $result  = "failure\n";
    $result .= "unknown mode\n";

    switch ($mode) {
    case 'checkauth' :
      $result = \Drupal\cmlservice\Controller\CmlCheckAuth::main($type);
      break;
    case 'init' :
      $result = \Drupal\cmlservice\Controller\CmlInit::main();
      break;
    case 'file' :
      $result = \Drupal\cmlservice\Controller\CmlFile::file($type);
      break;
    case 'query' :    // sale
      $result = \Drupal\cmlservice\Controller\CmlQuery::main($type);
      break;
    case 'import' :   // catalog
      $result = \Drupal\cmlservice\Controller\CmlImport::main($type);
      break;
    case 'success' :  // sale
      $result = \Drupal\cmlservice\Controller\CmlSuccess::main($type);
      break;
    default :
      self::debug(__FUNCTION__, "unknown mode");
      break;
    }

    self::debug(__FUNCTION__, "Ответ: " . $result);
    die($result);
    return array(
      '#markup' => $result,
    );
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
