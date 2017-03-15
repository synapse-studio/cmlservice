<?php

namespace Drupal\cmlservice\Xml;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Transliteration\PhpTransliteration;
use Drupal\cmlservice\Controller\GetLastCml;

/**
 * Controller routines for page example routes.
 */
class OffersHack extends ControllerBase {

  /**
   * Parce.
   */
  public static function hack($xmlObj) {
    $config = \Drupal::config('cmlservice.mapsettings');

    // Ищем import.xml в последней выгрузке.
    if ($config->get('hash-skip') && FALSE) {
      $data = FALSE;
      $products = FALSE;
      $xmlObjImport = new XmlObject();
      $trans = new PhpTransliteration();
      $cml_xml = GetLastCml::load()->field_cml_xml->getValue();

      foreach ($cml_xml as $xml) {
        $file = file_load($xml['target_id']);
        $filename = $file->getFilename();
        $filepath = drupal_realpath($file->getFileUri());
        if ($filename == 'import.xml') {
          $xmlObjImport->parseXmlFile($filepath);
          $xmlObjImport->parseXmlString($xmlObjImport->xmlString);
          $xmlObjImport->get('import', 'product');
          $products = $xmlObjImport->xmlfind;
        }
      }
      // Вытаскиваем из товаров нужное поле.
      $dop = [];
      if ($products) {
        foreach ($products as $key => $product) {
          $id = $product['Ид'];
          $key = 'ХарактеристикиТовара';
          $features = $xmlObjImport->prepare($product, $key, ['type' => []]);
          if ($features) {
            $dop[$id] = $features;
          }
        }
      }

      // Добавляем данные в массив $offers ($xmlObj->xmlfind).
      if (!empty($dop)) {
        $offers = $xmlObj->xmlfind;
        foreach ($offers as $key => $offer1c) {
          $id = $offer1c['Ид'];
          if (isset($dop[$id]) && $dop[$id]) {
            $offers[$key]['ХарактеристикиТовара'] = json_encode($dop[$id], JSON_UNESCAPED_UNICODE);
          }
        }
        $xmlObj->xmlfind = $offers;
      }

    }
  }

}
