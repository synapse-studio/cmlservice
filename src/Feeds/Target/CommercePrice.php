<?php

namespace Drupal\cmlservice\Feeds\Target;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\feeds\FieldTargetDefinition;
use Drupal\feeds\Plugin\Type\Target\FieldTargetBase;

/**
 * Defines a link field mapper.
 *
 * @FeedsTarget(
 *   id = "commerce_price",
 *   field_types = {"commerce_price"},
 * )
 */
class CommercePrice extends FieldTargetBase {

  /**
   * {@inheritdoc}
   */
  protected static function prepareTarget(FieldDefinitionInterface $field_definition) {
    return FieldTargetDefinition::createFromFieldDefinition($field_definition)
      ->addProperty('number')
      ->addProperty('currency_code');
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareValue($delta, array &$values) {
    if (isset($values['currency_code']) && isset($values['currency_code']['ЦенаЗаЕдиницу'])) {
      $values['number'] = trim($values['currency_code']['ЦенаЗаЕдиницу']);
      $code = $values['currency_code']['Валюта'];
      if ($code == 'руб') {
        $values['currency_code'] = 'RUB';
      }
      else {
        $values['currency_code'] = $code;
      }
    }
    else {
      $values['number'] = FALSE;
      $values['currency_code'] = FALSE;
    }
  }

}
