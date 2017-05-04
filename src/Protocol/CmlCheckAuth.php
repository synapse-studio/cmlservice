<?php

namespace Drupal\cmlservice\Protocol;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for page example routes.
 */
class CmlCheckAuth extends ControllerBase {

  /**
   * Main.
   */
  public static function main($type) {

    $result  = "failure\n";
    $result .= "auth error\n";

    $login = self::auth();
    if ($login) {
      $arr_cookie = self::cmlItemSet($type, $login);
      if (!empty($arr_cookie['nid']) and !empty($arr_cookie['uuid'])) {
        $result  = "success\n";
        $result .= "catalog\n";
        $result .= $arr_cookie['uuid'] . "\n";
      }
      else {
        $result  = "failure\n";
        $result .= "internal error\n";
        Cml::debug(__FUNCTION__, "Внутрення ошибка, не создался материал Cookie");
      }
    }
    else {
      Cml::debug(__FUNCTION__, "Ошибка авторизации. Base");
    }

    Cml::debug(__CLASS__, $result);
    return $result;
  }

  /**
   * Проверка авторизации по куке.
   */
  public static function check() {

    $result = 0;
    if (self::auth()) {
      if (isset($_COOKIE['catalog'])) {
        $arr_cookie = self::cmlItemGet($_COOKIE['catalog']);
        if (!empty($arr_cookie['nid']) and $arr_cookie['nid'] > 0) {
          $result = $arr_cookie['nid'];
        }
        else {
          Cml::debug(__FUNCTION__, "<pre>" . print_r($arr_cookie, TRUE) . "</pre>");
        }
      }
      else {
        Cml::debug(__FUNCTION__, "кука не установлена");
      }
    }
    else {
      $result .= "failure\n";
      $result .= "auth error\n";
      Cml::debug(__FUNCTION__, "Ошибка авторизации. Base");
    }

    return $result;
  }

  /**
   * Auth.
   */
  public static function auth() {
    $get = print_r($_GET, TRUE);
    $post = print_r($_POST, TRUE);
    $config = \Drupal::config('cmlservice.settings');
    $authorized = FALSE;
    if ($config->get('auth')) {
      $authorized = self::baseAuth();
    }
    else {
      Cml::debug(__FUNCTION__, "cml_auth = OFF: NO Need authentication");
      Cml::debug(__FUNCTION__, print_r($_SERVER, TRUE));
      $user = self::baseAuthUser();
      if ($user) {
        $authorized = $user['name'];
        Cml::debug(__FUNCTION__, "1C user:" . $authorized);
      }
      else {
        $authorized = TRUE;
        Cml::debug(__FUNCTION__, "1C user NOT SET");
      }
      return $authorized;
    }
  }

  /**
   * Base Auth User.
   */
  public static function baseAuthUser() {
    $user = FALSE;
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
      $auth = array();
      if (preg_match('/Basic\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $auth)) {
        list($auth_name, $auth_pass) = explode(':', base64_decode($auth[1]));
        $user = [
          'name' => $auth_name,
          'pass' => $auth_pass,
        ];
      }
    }
    return $user;
  }

  /**
   * Базовая HTTP Авторизация.
   *
   * #masdzen 20120705.
   * RewriteCond %{REQUEST_URI} !cron.php
   * RewriteCond %{HTTP:Authorization} ^Basic.*
   * RewriteRule (.*) index.php?Authorization=%{HTTP:Authorization} [QSA,L].
   */
  public static function baseAuth() {
    $get = print_r($_GET, TRUE);
    $post = print_r($_POST, TRUE);
    $config = \Drupal::config('cmlservice.settings');
    if ($config->get('auth')) {
      $authorized = FALSE;
      $auth = self::baseAuthUser();
      if ($auth) {
        $config_name = $config->get('auth-user');
        $config_pass = $config->get('auth-pass');
        if (($auth['name'] == $config_name) & ($auth['pass'] == $config_pass)) {
          $authorized = $auth['name'];
        }
        else {
          Cml::debug(__FUNCTION__,
              t('@login:@pass - wrong login pair', array('@login' => $auth['name'], '@pass' => $auth['pass'])));
          Cml::debug(__FUNCTION__,
              t("base authorized :\nget = @get\npost = @post", array('@get' => $get, '@post' => $post)));
        }
      }
      return $authorized;
    }
    else {
      return TRUE;
    }
  }

  /**
   * Создать новую куку.
   */
  public static function cmlItemSet($type, $login) {
    $storage = \Drupal::entityManager()->getStorage('cml');
    $cml = $storage->create([
      'field_cml_login' => $login,
      'field_cml_type' => $type,
      'field_cml_ip' => \Drupal::request()->getClientIp(),
    ]);
    $cml->save();
    $result = [
      'nid'  => $cml->id(),
      'uuid' => $cml->uuid->value,
    ];
    return $result;
  }

  /**
   * Проверить куку.
   */
  public static function cmlItemGet($uuid) {
    $entity = \Drupal::entityManager()->loadEntityByUuid('cml', $uuid);
    $result = [
      'nid'  => $entity->id(),
      'uuid' => $entity->uuid->value,
    ];
    return $result;
  }

}
