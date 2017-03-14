<?php

namespace Drupal\cmlservice\Feeds\Parser\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Plugin\Type\ExternalPluginFormBase;

/**
 * Provides a form on the feed edit page for the CsvParser.
 */
class CustomParcerFeedForm extends ExternalPluginFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FeedInterface $feed = NULL) {
    $feed_config = $feed->getConfigurationFor($this->plugin);
    $form['parser']['limit'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Limit'),
      '#default_value' => $feed_config['limit'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state, FeedInterface $feed = NULL) {
    $parser = $form_state->getValue('parser');
    if ($parser['limit']) {
      drupal_set_message('limit != ' . $parser['limit'] . '. Not ready yet, can\' feed->setConfig()');
    }
  }

}
