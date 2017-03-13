<?php

namespace Drupal\cmlservice\Feeds\Parser\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\Plugin\Type\ExternalPluginFormBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The configuration form for the CSV parser.
 */
class ProductParcerForm extends ExternalPluginFormBase implements ContainerInjectionInterface {

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
    $form['offers'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Attach offers'),
      '#default_value' => $this->plugin->getConfiguration('offers'),
    ];
    $form['images'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Attach images'),
      '#default_value' => $this->plugin->getConfiguration('images'),
    ];
    return $form;
  }

}
