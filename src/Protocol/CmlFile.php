<?php

namespace Drupal\cmlservice\Protocol;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;

/**
 * CmlFile.
 */
class CmlFile extends ControllerBase {

  /**
   * File.
   */
  public static function file($type = 'import') {
    $result = '';

    if (CmlCheckAuth::auth() || TRUE) {
      $cml_id = CmlCheckAuth::check();
      if ($cml_id) {
        if (isset($_GET['filename'])) {
          // Cохраняем файл.
          $filename = $_GET['filename'];
          Cml::debug(__CLASS__, $filename);

          $xml = FALSE;
          // 'import.xml', 'offers.xml'.
          if (strpos($filename, '.xml')) {
            $filepath = 'public://cml-files/' . $type . '/' . $cml_id . '/';
            $xml = TRUE;
          }
          else {
            $filepath = 'public://cml-files/img';
            Cml::debug(__CLASS__, 'GET:' . $filename);
            $path = explode('/', $filename);
            $filename = array_pop($path);
            $filepath = $filepath . '/' . implode('/', $path) . '/';
          }

          if ($content = file_get_contents('php://input')) {
            file_prepare_directory($filepath, FILE_CREATE_DIRECTORY);
            $file = file_save_data($content, $filepath . $filename, FILE_EXISTS_REPLACE);
            $file->save();

            if ($file->id()) {
              $file->display = 1;
              $config = \Drupal::config('cmlservice.settings');
              if ($xml) {
                $cml = \Drupal::entityManager()->getStorage('cml')->load($cml_id);
                $xmlfiles = $cml->field_cml_xml->getValue();
                $xmlfiles[] = ['target_id' => $file->id()];
                $cml->field_cml_xml->setValue($xmlfiles);
                $cml->save();
              }
              $result = "success\n";
              Cml::debug(__CLASS__, $cml_id . " upload " . $filepath);
            }
            else {
              $result  = "failure\n";
              $result .= "Error during writing file.\n";
              Cml::debug(__CLASS__, "Ошибка при записи файла. Не нашли куку авторизации");
            }
          }
          else {
            $result  = "failure\n";
            $result .= "Error during writing file.\n";
            Cml::debug(__CLASS__, "Ошибка при записи файла. Не нашли переданного файла в потоке.");
          }
        }
        else {
          $result  = "failure\n";
          $result .= "filename error\n";
          Cml::debug(__CLASS__, "Ошибка загрузки файла, не определено имя файла. Import file");
        }

      }
      else {
        $result .= "failure\n";
        $result .= "auth error\n";
        Cml::debug(__CLASS__, "Ошибка авторизации. Cookie.");
      }
    }
    else {
      $result .= "failure\n";
      $result .= "auth error\n";
      Cml::debug(__CLASS__, "Ошибка авторизации. Base.");
    }

    Cml::debug(__CLASS__, "file result:\n" . $result);
    return $result;
  }

}
