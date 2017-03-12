<?php

namespace Drupal\cmlservice\Feeds\Fetcher\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\Plugin\Type\ExternalPluginFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a directory fetcher.
 */
class CommerceMLFetcherForm extends ExternalPluginFormBase implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('stream_wrapper_manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['target-id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Target id (cml nid)'),
      '#default_value' => $this->plugin->getConfiguration('target-id'),
      '#description' => $this->t('Leave blank to use last'),
    ];
    $form['file-name'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('File'),
      '#default_value' => $this->plugin->getConfiguration('file-name'),
      '#required' => TRUE,
      '#options' => [
        'offers' => 'offers.xml',
        'import' => 'import.xml',
      ],
    ];
    return $form;
  }

}
