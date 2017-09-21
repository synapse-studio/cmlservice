<?php

namespace Drupal\cmlservice\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\cmlservice\Xml\XmlObject;
use Drupal\cmlservice\Xml\TovarParcer;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\node\Entity\Node;
use Symfony\Component\Yaml\Yaml;

/**
 * Tovar Parcer.
 */
class Tovar extends ControllerBase {

  /**
   * Page export.
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
        if ($filename == 'import.xml') {
          $xmlObj = new XmlObject();
          $xmlObj->parseXmlFile($filepath);
          $data = TovarParcer::parce($xmlObj->xmlString);
        }
      }
    }
    if (!empty($data)) {
      $i = 1;
      $result .= '<pre>';
      foreach ($data as $key => $value) {
        $result .= $i++ . " - " . $key . "\n";
        foreach ($value['product'] as $k => $v) {
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
        $result .= "Offers:\n";
        $result .= Yaml::dump($value['offers']);
        $result .= "\n\n\n";
      }
      $result .= '</pre>';
    }
    return [
      '#markup' => $result,
    ];
  }

  public static function checkVariation(Node $tovar, $selfSave = FALSE) {
    $field = $tovar->field_tovar_variation;
    $variationIds = [];
    for ($i = 0; $i < $field->count(); $i++) {
      $variationIds[] = $field->get($i)->getValue()['target_id'];
    }
    if (count($variationIds)) {
      $variations = entity_load_multiple('commerce_product_variation', $variationIds);
      $exist = FALSE;
      foreach ($variations as $variation) {
        if ($exist) {
          break;
        }
        if ($variation->field_product_variation_value->value != 0) {
          $exist = TRUE;
        }
      }
      $oldExist = isset($tovar->field_tovar_stock->value) ? strtolower($tovar->field_tovar_stock->value) : 'false';
      $exist = $exist ? 'true' : 'false';
      if ($oldExist != $exist) {
        $tovar->set('field_tovar_stock', $exist);
        if (!$selfSave) {
          $tovar->save();
        }
      }
    }
  }

  public static function changeAfterVariationSave(ProductVariation $variation) {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'tovar')
      ->condition('field_tovar_variation', $variation->id());
    $result = $query->execute();
    if (count($result)) {
      $entitys = entity_load_multiple('node', $result);
      foreach ($entitys as $tovar) {
        self::checkVariation($tovar);
      }
    }
  }

}
