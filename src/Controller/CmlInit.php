<?php

namespace Drupal\cmlservice\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\cmlservice\Controller\Cml;
use Drupal\cmlservice\Controller\CmlCheckAuth;

/**
 * Controller routines for page example routes.
 */
class CmlInit extends ControllerBase {

  public static function main() {

    $result = '';

    if (CmlCheckAuth::auth()) {

      if (CmlCheckAuth::check()) {
        $config = \Drupal::config('cmlservice.settings');
        $result .= "zip=" . ($config->get('zip') ? 'yes' : 'no') . "\n";
        $result .= "file_limit=" . $config->get('file-limit') . "\n";
      } else {
        $result .= "failure\n";
        $result .= "auth error\n";
        Cml::debug(__CLASS__, "Ошибка авторизации. Cookie.");
      }
    } else {
      $result .= "failure\n";
      $result .= "auth error\n";
      Cml::debug(__CLASS__, "Ошибка авторизации. Base.");
    }

    Cml::debug(__CLASS__, "init result:\n" . $result);
    return $result;
  }

}
