<?php

namespace Drupal\cmlservice\Feeds\Processor;

use Drupal\feeds\Feeds\Processor\EntityProcessorBase;

/**
 * Defines a user processor.
 *
 * Creates users from feed items.
 *
 * @FeedsProcessor(
 *   id = "entity:commerce_product_variation",
 *   title = @Translation("Product variation"),
 *   description = @Translation("Product variations."),
 *   entity_type = "commerce_product_variation",
 *   arguments = {"@entity.manager", "@entity.query", "@entity_type.bundle.info"},
 *   form = {
 *     "configuration" = "Drupal\feeds\Feeds\Processor\Form\DefaultEntityProcessorForm",
 *     "option" = "Drupal\feeds\Feeds\Processor\Form\EntityProcessorOptionForm",
 *   },
 * )
 */
class CommerceProductVariationProcessor extends EntityProcessorBase {

}
