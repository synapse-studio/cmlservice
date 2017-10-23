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
    if (CmlCheckAuth::auth()) {
      $cmlid = self::queryLast();
      $migrations = self::getMigrations();
      if (is_numeric($cmlid) && !empty($migrations)) {
        Cml::debug(__CLASS__, "processData: $cmlid");
        return self::processData($migrations, $cmlid);
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
   * Import.
   */
  public static function processData($migrations, $cmlid, $debug = FALSE) {
    $result = 'progress';
    $config = \Drupal::configFactory()->getEditable('cmlservice.settings');
    $id = \Drupal::config('cmlservice.settings')->get('current-import');
    if (is_numeric($cmlid)) {
      $config->set('current-import', $cmlid)->save();
      $cml = \Drupal::entityManager()->getStorage('cml')->load($cmlid);
      $time = $cml->created->value;
      $status = $cml->field_cml_status->value;
      if ($status == 'new') {
        $config->set('current-import', $cmlid)->save();
        $cml->field_cml_status->setValue('progress');
        $cml->save();
        // Good.
        $command = "nohup drush mi --group=cml --update > /dev/null";
        // Bad.
        $command = "drush mi --group=cml --update > /dev/null 2>/dev/null &";
        if (!$debug) {
          exec($command);
        }
      }
      if ($status == 'progress') {
        $config->set('current-import', $cmlid)->save();
        $progress = 'Idle';
        foreach ($migrations as $key => $migration) {
          if ($migration['status'] != 'Idle') {
            $progress = $migration['status'];
          }
        }
        if ($progress != 'Idle') {
          // Proccess.
          // if too long.
          $min = $id = \Drupal::config('cmlservice.settings')->get('import-time');
          empty($min) ?? $min = 60;
          if ($cml->changed->value + 60 * $min < REQUEST_TIME) {
            $date1 = format_date($cml->changed->value + 60 * $min, 'custom', "dM H:i:s");
            $date2 = format_date(REQUEST_TIME, 'custom', "dM H:i:s");
            \Drupal::logger('cmlservice')->error("cml:$cmlid import timeout failure. $date1 < $date2");
            $config->set('current-import', FALSE)->save();
            $cml->field_cml_status->setValue('failure');
            //$cml->save();
            //$result = 'failure';
          }
        }
        else {
          Cml::debug(__CLASS__, "$cmlid: success");
          $config->set('current-import', FALSE)->save();
          $cml->field_cml_status->setValue('success');
          $cml->save();
        }
      }
      if ($status == 'success') {
        $result = 'success';
      }
      Cml::debug(__CLASS__, "$cmlid: $status: $result");
    }
    return $result;
  }

  /**
   * Query.
   */
  public static function queryLast() {
    $query = \Drupal::entityQuery('cml');
    $query->condition('field_cml_xml', 'NULL', '!=')
      ->condition('field_cml_status', ['success', 'failure'], 'NOT IN')
      ->condition('type', 'catalog')
      ->sort('created', 'ASC');
    $result = $query->execute();
    return array_shift($result);
  }

  /**
   * Get migrations.
   */
  public static function getMigrations() {
    $migrations = [];
    $manager = FALSE;
    try {
      $manager = \Drupal::service('plugin.manager.migration');
    }
    catch (\Exception $e) {
      return FALSE;
    }
    if ($manager) {
      $plugins = $manager->createInstances([]);
      if (!empty($plugins)) {
        foreach ($plugins as $id => $migration) {
          if ($migration->migration_group == 'cml') {
            $source_plugin = $migration->getSourcePlugin();
            $map = $migration->getIdMap();
            $migrations[$id] = [
              'id' => $migration->id(),
              'label' => $migration->label(),
              'group' => $migration->get('migration_group'),
              'status' => $migration->getStatusLabel(),
              'total' => $source_plugin->count(),
              'imported' => (int) $map->importedCount(),
              'messages' => $map->messageCount(),
              'last' => \Drupal::keyValue('migrate_last_imported')->get($migration->id(), FALSE),
            ];
          }
        }
      }
    }
    return $migrations;
  }

}
