<?php

namespace Drupal\cmlservice\Protocol;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\Yaml\Yaml;
use Drupal\feeds\Entity\Feed;

/**
 * Controller routines for page example routes.
 */
class CmlImport extends ControllerBase {

  /**
   * Main.
   */
  public static function main() {
    if (CmlCheckAuth::auth()) {
      $query = \Drupal::entityQuery('cml');
      $query->condition('field_cml_xml', 'NULL', '!=')
        ->condition('field_cml_status', 'done', '!=')
        ->sort('field_cml_date', 'ASC')
        ->range(0, 1);
      $result = $query->execute();
      if (count($result)) {
        $config = \Drupal::config('cmlservice.settings');
        $feedsOrder = Yaml::parse($config->get('feeds-order'));
        if (isset($feedsOrder)) {
          return self::processData($feedsOrder, array_shift($result));
        }
      }
      return 'success';
    }
    else {
      $result .= "failure\n";
      $result .= "auth error\n";
      Cml::debug(__CLASS__, "Ошибка авторизации. Base.");
      return $result;
    }
  }
  
  /**
   * Обрабатывает полученные данные.
   */
  public static function processData($feedsOrder, $cmlId) {
    $cml = \Drupal::entityManager()->getStorage('cml')->load($cmlId);
    $cmlStatus = $cml->field_cml_status->value;
    if (isset($feedsOrder[$cmlStatus])) {
      $feedSets = $feedsOrder[$cmlStatus];
      if (is_numeric($feedSets['feedId'])) {
        $result = self::feedCronRun($feedSets['feedId'], $cml->getCreatedTime());
        switch ($result) {
          case 'imported':
            $cml->field_cml_status = $feedSets['next'];
            $cml->save();
            break;
        }
      }
      else {
        $cml->field_cml_status = $feedSets['next'];
        $cml->save();
      }
      return 'progress';
    }
    else {
      return 'failure';
    }
  }

  /**
   * Дать задачу на запуск фидса.
   */
  public static function feedCronRun($id, $created) {
    $feed = Feed::load($id);
    if (isset($feed->field_feeds_import_offset->value)) {
      $offset = $feed->field_feeds_import_offset->value > 0 ? TRUE : FALSE;
    }
    else {
      $offset = FALSE;
    }
    if (($feed->getImportedTime() > $created) && !$offset) {
      return 'imported';
    }
    if (!$feed->isLocked()) {
      $queue = \Drupal::queue('feeds_feed_import:' . $feed->bundle());
      $items = $queue->numberOfItems();
      if ($items == 0) {
        if ($queue->createItem($feed)) {
          $feed->setQueuedTime(REQUEST_TIME);
          $feed->save();
          return 'created';
        }
        else {
          return 'failure';
        }
      }
      else {
        return 'exist';
      }
    }
    else {
      return 'locked';
    }
  }

}
