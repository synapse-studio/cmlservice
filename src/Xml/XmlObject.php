<?php

namespace Drupal\cmlservice\Xml;

/**
 * Class XmlObject.
 */
class XmlObject {
  /**
   * Props.
   *
   * @var xmlString
   *      stages
   *      xmlImportMapping import.xml
   *      xmlOffersMapping offers.xml
   */
  public $xmlString = NULL;
  public $xmlArray = [0 => []];
  public $xmlfind = FALSE;
  public $stages = [
    'category',
    'feature',
    'product',
    'price',
    'sku',
    'stock',
    'offer',
    'order',
    'image',
  ];
  public $xmlImportMapping = [
    'category' => 'Классификатор/Группы/Группа',
    'feature'  => 'Классификатор/Свойства',
    'price'    => 'Классификатор/ТипыЦен',
    'stock'    => 'Классификатор/Склады',
    'product'  => 'Каталог/Товары/Товар',
  ];
  public $xmlOffersMapping = [
    'price'   => 'ПакетПредложений/ТипыЦен',
    'stock'   => 'ПакетПредложений/Склады',
    'feature' => 'ПакетПредложений/Свойства',
    'offer'   => 'ПакетПредложений/Предложения/Предложение',
  ];
  public $productMaps = [
    'ЗначенияРеквизитов'   => 'ЗначениеРеквизита',
    'ЗначенияСвойств'   => 'ЗначенияСвойства',
    'ХарактеристикиТовара' => 'ХарактеристикаТовара',
    'СтавкиНалогов'   => 'СтавкаНалога',
    'Цены'   => 'Цена',
  ];

  /**
   * Parse_xml_file.
   */
  public function parseXmlFile($file_uri) {
    $this->xmlString = NULL;

    if (is_file($file_uri) && is_readable($file_uri)) {
      $my_file = fopen($file_uri, "r");
      while ($my_xml_input = fread($my_file, filesize($file_uri))) {
        $this->xmlString .= $my_xml_input;
      }
      fclose($my_file);

    }
    else {
      trigger_error("supplied argument is not a URI to a (readable) file", E_USER_ERROR);
    }

  }

  /**
   * Find.
   */
  public function prepare($product, $key, $map) {
    $result = NULL;
    if (isset($product[$key]) && !isset($map['skip']) && $product[$key] !== NULL) {
      $field = $product[$key];
      if (!isset($map['type'])) {
        if (!is_array($field)) {
          $result = $field;
        }
      }
      elseif (is_array($map['type'])) {
        $result = [];
        if (isset($this->productMaps[$key]) && $m = $this->productMaps[$key]) {
          $result = $this->arrayNormalize($field[$m]);
          if ($key == 'Цены') {
            $result = [array_shift($result)];
          }
        }
        elseif ($key == 'Группы') {
          foreach ($this->arrayNormalize($field) as $group) {
            $result[] = $group['Ид'];
          }
        }
        else {
          if (isset($map['type']['inside'])) {
            $result = $this->arrayNormalize($field[$map['type']['inside']]);
          }
          else {
            $result = $this->arrayNormalize($field);
          }
        }
      }
      elseif ($map['type'] == 'attr') {
        $attr = $map['attr'];
        if (isset($field['@attributes'][$attr])) {
          $result = $field['@attributes'][$attr];
        }
      }
    }

    return $result;
  }

  /**
   * Find.
   */
  public function find($map) {
    $query = explode("/", $map);
    $result = FALSE;
    $this->xmlfind = $this->xmlArray;
    foreach ($query as $q) {
      if (isset($this->xmlfind[$q])) {
        $this->xmlfind = $this->xmlfind[$q];
      }
      else {
        $this->xmlfind = FALSE;
      }
    }
    return $result;
  }

  /**
   * Find.
   */
  public function get($type, $key) {
    $map = FALSE;
    if ($type == 'import') {
      $mapping = $this->xmlImportMapping;
    }
    elseif ($type == 'offers') {
      $mapping = $this->xmlOffersMapping;
    }
    if (isset($mapping[$key])) {
      $map = $mapping[$key];
      $this->queryMap = $map;
    }

    $this->find($map);
    return $map;
  }

  /**
   * Parse_xml_file.
   */
  public function parseXmlString($xml_string) {
    $xml = simplexml_load_string($xml_string);
    if (!$xml) {
      trigger_error("data can not be parsed", E_USER_ERROR);
    }
    $json = json_encode($xml, JSON_FORCE_OBJECT);
    $this->xmlArray = json_decode($json, TRUE);
    $this->xmlString = 'parse DONE && string remove';
    return $this->xmlArray;
  }

  /**
   * HELPER: Array Normalize.
   */
  public static function mapMerge($array1, $array2) {
    if (!is_array($array1)) {
      $array1 = [];
    }
    if (!is_array($array2)) {
      $array2 = [];
    }
    $map = array_merge($array1, $array2);
    return $map;
  }

  /**
   * HELPER: Array Normalize.
   */
  public static function arrayNormalize($array) {
    $norm = FALSE;
    if (is_string($array)) {
      $norm = FALSE;
    }
    else {
      foreach ($array as $key => $value) {
        if (is_numeric($key)) {
          $norm = TRUE;
        }
      }
    }

    if ($norm) {
      return $array;
    }
    else {
      return [$array];
    }
  }

}
