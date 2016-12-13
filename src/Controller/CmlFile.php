<?php

namespace Drupal\cmlservice\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\cmlservice\Controller\Cml;
use Drupal\cmlservice\Controller\CmlCheckAuth;
use Drupal\file\Entity\File;

/**
 * Controller routines for page example routes.
 */
class CmlFile extends ControllerBase {

  public static function file($type = 'import') {
    $result = '';

    //if (CmlCheckAuth::auth()) {
      $nid = CmlCheckAuth::check();
      if ($nid) {
        if (isset($_GET['filename'])) {
          //сохраняем файл
          $filename = $_GET['filename'];
          Cml::debug(__CLASS__, $filename);
          //$filepath = 'public://cml-files/'.$type.'/' . variable_get('cml_images_path', 'cml');

          $xml = false;
          if(strpos($filename, '.xml')){  // 'import.xml', 'offers.xml'
            $filepath = 'public://cml-files/'.$type . '/' . $nid . '/';
          	$xml = true;
          }else{
            $filepath = 'public://cml-files/img/';
          	Cml::debug(__CLASS__, 'GET:' . $filename);
          	$path = explode('/', $filename);
          	// = end ($path);
          	$filename = array_pop($path);
          	$filepath = $filepath . '/' . implode('/', $path) . '/';
          }

          //Cml::debug(__CLASS__, $filepath);
          //Cml::debug(__CLASS__, 'GET:' . $_GET['filename']);


          if ($content = file_get_contents('php://input')) {
            file_prepare_directory($filepath, FILE_CREATE_DIRECTORY);
            $file = file_save_data($content, $filepath . $filename, FILE_EXISTS_REPLACE);
            $file->save();

            if ($file->id()) {
              $file -> display = 1;
              $config = \Drupal::config('cmlservice.settings');
              if($xml){
                $node = node_load($nid);
                $cml_xml = $node -> field_cml_xml -> getValue();
                $cml_xml[] = ['target_id' => $file->id()];
                $node -> field_cml_xml -> setValue($cml_xml);
                $node -> save();
              //$statuses = $ewrapper -> field_cookie_status_import -> value();
              //$statuses[] = 'start';
              //$ewrapper -> field_cookie_status_import -> set($statuses);
              }
              $result = "success\n";
              Cml::debug(__CLASS__, $nid . " upload " . $filepath);
            } else {
              $result  = "failure\n";
              $result .= "Error during writing file.\n";
              Cml::debug(__CLASS__, "Ошибка при записи файла. Не нашли куку авторизации");
            }
          } else {
            $result  = "failure\n";
            $result .= "Error during writing file.\n";
            Cml::debug(__CLASS__, "Ошибка при записи файла. Не нашли переданного файла в потоке.");
          }
        } else {
          $result  = "failure\n";
          $result .= "filename error\n";
          Cml::debug(__CLASS__, "Ошибка загрузки файла, не определено имя файла. Import file");
        }




      } else {
        $result .= "failure\n";
        $result .= "auth error\n";
        Cml::debug(__CLASS__, "Ошибка авторизации. Cookie.");
      }
    //}
    //else {
    //  $result .= "failure\n";
    //  $result .= "auth error\n";
    //  Cml::debug(__CLASS__, "Ошибка авторизации. Base.");
    //}

    //Cml::debug(__CLASS__, "file result:\n" . $result);
    return $result;
  }

}
