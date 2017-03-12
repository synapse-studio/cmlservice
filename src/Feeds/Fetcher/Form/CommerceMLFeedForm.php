<?php

namespace Drupal\cmlservice\Feeds\Fetcher\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Plugin\Type\ExternalPluginFormBase;

/**
 * Provides a form on the feed edit page for the DirectoryFetcher.
 */
class CommerceMLFeedForm extends ExternalPluginFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FeedInterface $feed = NULL) {

    $form['target'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Target id (cml nid)'),
      '#default_value' => $feed->getSource(),
      '#description' => $this->t('Leave blank to use FeedsType Settings, "last" to use forse last.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state, FeedInterface $feed = NULL) {
    $feed->setSource($form_state->getValue('target'));
  }

}
