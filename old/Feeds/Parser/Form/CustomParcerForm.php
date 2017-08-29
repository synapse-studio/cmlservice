<?php

namespace Drupal\cmlservice\Feeds\Parser\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\Plugin\Type\ExternalPluginFormBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The configuration form for the CSV parser.
 */
class CustomParcerForm extends ExternalPluginFormBase implements ContainerInjectionInterface {

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
    $form['query'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Query'),
      '#default_value' => $this->plugin->getConfiguration('query'),
      '#description' => 'Example: "Классификатор/Бренды/Бренд" or "Классификатор/Категории"',
    ];
    $form['limit'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Limit $rows'),
      '#default_value' => $this->plugin->getConfiguration('limit'),
      '#description' => 'Limit $rows to debug',
    ];
    return $form;
  }

}
