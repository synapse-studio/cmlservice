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
 *   field_types = {"commerce_price"}
 * )
 */
class CommercePrice extends FieldTargetBase {

  /**
   * {@inheritdoc}
   */
  protected static function prepareTarget(FieldDefinitionInterface $field_definition) {
    return FieldTargetDefinition::createFromFieldDefinition($field_definition)
      ->addProperty('amount')
      ->addProperty('currency_code');
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareValue($delta, array &$values) {
    $values['amount'] = trim($values['amount']);
    if (!is_numeric($values['amount'])) {
      $values['amount'] = FALSE;
    }
  }

}
