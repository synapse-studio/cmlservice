<?php

namespace Drupal\cmlservice\Protocol;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for page example routes.
 */
class Cml extends ControllerBase {

  /**
   * Main.
   */
  public static function cml() {
    self::debug(__FUNCTION__, "test");

    $get = print_r($_GET, TRUE);
    $post = print_r($_POST, TRUE);

    $type = '';
    $mode = '';
    if (isset($_GET['type']) and isset($_GET['mode'])) {
      $type = $_GET['type'];
      $mode = $_GET['mode'];
    }
    self::debug(__FUNCTION__, t("q: type=@type|mode=@mode", ['@type' => $type, '@mode' => $mode]));

    $result  = "failure\n";
    $result .= "unknown mode\n";

    switch ($mode) {
      case 'checkauth':
        $result = CmlCheckAuth::main($type);
        break;

      case 'init':
        $result = CmlInit::main();
        break;

      case 'file':
        $result = CmlFile::file($type);
        break;

      case 'query':
        $result = CmlQuery::main($type);
        break;

      case 'import':
        $result = CmlImport::main($type);
        break;

      case 'success':
        $result = CmlSuccess::main($type);
        break;

      default:
        self::debug(__FUNCTION__, "unknown mode");
        break;
    }

    self::debug(__FUNCTION__, "Ответ: " . $result);
    if (TRUE) {
      die($result);
    }

    return [
      '#markup' => $result,
    ];
  }

  /**
   * Debug.
   */
  public static function debug($function, $message) {
    $debug = FALSE;
    $config = \Drupal::config('cmlservice.settings');
    if ($config->get('debug')) {
      \Drupal::logger($function)->notice($message);
      $debug = TRUE;
    }
    return $debug;
  }

}
