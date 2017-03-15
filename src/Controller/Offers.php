<?php

namespace Drupal\cmlservice\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\cmlservice\Xml\XmlObject;
use Drupal\cmlservice\Xml\OffersParcer;

/**
 * Controller routines for page example routes.
 */
class Offers extends ControllerBase {

  /**
   * Page import.
   */
  public function page($cml) {
    $result = __CLASS__;
    $result .= '<br />nid=' . $cml;
    $cml_xml = GetLastCml::load($cml)->field_cml_xml->getValue();
    $data = FALSE;

    if (!empty($cml_xml)) {
      foreach ($cml_xml as $xml) {
        $file = file_load($xml['target_id']);
        $filename = $file->getFilename();
        $filepath = drupal_realpath($file->getFileUri());
        if ($filename == 'offers.xml') {
          $xmlObj = new XmlObject();
          $xmlObj->parseXmlFile($filepath);
          $data = OffersParcer::parce($xmlObj->xmlString);
        }
      }
    }
    if (!empty($data)) {
      $i = 1;
      $result .= '<pre>';
      foreach ($data as $key => $value) {
        $result .= $i++ . " - " . $key . "\n";
        foreach ($value as $k => $v) {
          $result .= "    " . $k . ": ";
          if (!is_array($v)) {
            $result .= $v;
          }
          else {
            $result .= json_encode($v, JSON_UNESCAPED_UNICODE);
          }
          $result .= "\n";
        }
        $result .= "\n";
      }
      $result .= '</pre>';
    }
    return [
      '#markup' => $result,
    ];
  }

}
