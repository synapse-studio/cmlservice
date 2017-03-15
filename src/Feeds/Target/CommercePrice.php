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
    if (isset($values['number']) && isset($values['number']['ЦенаЗаЕдиницу'])) {
      $storage = \Drupal::entityManager()->getStorage('commerce_currency');
      $codes = array_keys($storage->loadMultiple());
      $price = trim($values['number']['ЦенаЗаЕдиницу']);
      $code = $values['number']['Валюта'];
      if (!in_array($code, $codes)) {
        $code = array_shift($codes);
      }
      $values = [
        'number' => $price,
        'currency_code' => $code,
      ];
    }
    else {
      $values = [
        'number' => FALSE,
        'currency_code' => FALSE,
      ];
    }
  }

}
