<?php

namespace Drupal\cmlservice\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\cmlservice\Xml\XmlObject;
use Drupal\cmlservice\Xml\CatalogParcer;

/**
 * Controller routines for page example routes.
 */
class Catalog extends ControllerBase {

  /**
   * Page tree.
   */
  public function page($cml) {
    $result = '';
    $data = $this->data($cml);
    if ($data) {
      $result .= '<div id="jstree">';
      $result .= $this->renderGroups($data, TRUE);
      $result .= '</div>';
    }

    return array(
      '#markup' => $result,
      '#attached' => ['library' => ['cmlservice/cmlservice.jstree']],
    );
  }

  /**
   * Json import.
   */
  public function data($cml) {
    $cml_xml = GetLastCml::load($cml)->field_cml_xml->getValue();
    $data = FALSE;
    $groups = FALSE;
    if (!empty($cml_xml)) {
      foreach ($cml_xml as $xml) {
        $file = file_load($xml['target_id']);
        $filename = $file->getFilename();
        $filepath = drupal_realpath($file->getFileUri());
        if ($filename == 'import.xml') {
          $xmlObj = new XmlObject();
          $xmlObj->parseXmlFile($filepath);
          $data = CatalogParcer::parce($xmlObj->xmlString, FALSE);
        }
      }
    }
    return $data;
  }

  /**
   * Render.
   */
  public function renderGroups($groups, $parent = FALSE) {
    $output = '<ul>';

    if (!empty($groups)) {
      $groups = XmlObject::arrayNormalize($groups);

      foreach ($groups as $group) {
        $data = FALSE;
        if ($parent) {
          $data = " data-jstree='{ \"opened\" : true }' ";
        }
        $output .= '<li ' . $data . '>';
        $output .= $group['Наименование'];
        if (!empty($group['Группы']['Группа'])) {
          $output .= self::renderGroups($group['Группы']['Группа']);
        }
        $output .= '</li>';
      }
      $output .= '</ul>';
    }
    return $output;
  }

}
