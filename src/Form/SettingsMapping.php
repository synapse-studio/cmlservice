<?php

namespace Drupal\cmlservice\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the form controller.
 */
class SettingsMapping extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cmlservice_mapsettings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['cmlservice.mapsettings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('cmlservice.mapsettings');

    $form['config'] = [
      '#type' => 'details',
      '#title' => $this->t('General settings'),
    ];
    $form['config']['tovar-standart'] = [
      '#title' => $this->t('Product Standart'),
      '#type' => 'textarea',
      '#default_value' => $config->get('tovar-standart'),
      '#rows' => 12,
    ];
    $form['config']['offers-standart'] = [
      '#title' => $this->t('Offers'),
      '#type' => 'textarea',
      '#default_value' => $config->get('offers-standart'),
      '#rows' => 6,
    ];
    $form['tovar-dop'] = [
      '#title' => $this->t('Product Dop'),
      '#type' => 'textarea',
      '#default_value' => $config->get('tovar-dop'),
      '#rows' => 10,
    ];
    $form['offers-dop'] = [
      '#title' => $this->t('Offers'),
      '#type' => 'textarea',
      '#default_value' => $config->get('offers-dop'),
      '#rows' => 5,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('cmlservice.mapsettings');
    $config
      ->set('tovar-standart', $form_state->getValue('tovar-standart'))
      ->set('tovar-dop', $form_state->getValue('tovar-dop'))
      ->set('offers-standart', $form_state->getValue('offers-standart'))
      ->set('offers-dop', $form_state->getValue('offers-dop'))
      ->save();
  }

}
