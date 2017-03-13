<?php

namespace Drupal\cmlservice\Feeds\Parser\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Plugin\Type\ExternalPluginFormBase;

/**
 * Provides a form on the feed edit page for the CsvParser.
 */
class ProductParcerFeedForm extends ExternalPluginFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FeedInterface $feed = NULL) {
    $feed_config = $feed->getConfigurationFor($this->plugin);

    return $form;
  }

}
