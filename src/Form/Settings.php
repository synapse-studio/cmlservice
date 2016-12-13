<?php

namespace Drupal\cmlservice\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Implements the form controller.
 */
class Settings extends ConfigFormBase {

  public function getFormId() {
    return 'cmlservice_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['cmlservice.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('cmlservice.settings');


    $form['cml'] = [
      '#type' => 'details',
      '#title' => $this->t('General settings'),
      '#open' => TRUE,
    ];
    $form["cml"]['auth'] = array(
      '#title' => $this->t('Need authentication'),
      '#type' => 'checkbox',
      '#maxlength' => 20,
      '#required' => FALSE,
      '#size' => 15,
      '#default_value' => $config->get('auth'),
    );
    $form["cml"]['debug'] = array(
      '#title' => $this->t('Debug mode'),
      '#type' => 'checkbox',
      '#maxlength' => 20,
      '#required' => FALSE,
      '#size' => 15,
      '#default_value' => $config->get('debug'),
    );
    $form['cml']['auth_user'] = [
      '#title' => $this->t('Auth username'),
      '#default_value' => $config->get('auth-user'),
      '#maxlength' => 20,
      '#required' => FALSE,
      '#size' => 15,
      '#type' => 'textfield',
      '#description' => $this->t(''),
    ];
    $form['cml']['auth_pass'] = [
      '#title' => $this->t('Auth password'),
      '#default_value' => $config->get('auth-pass'),
      '#maxlength' => 20,
      '#required' => FALSE,
      '#size' => 15,
      '#type' => 'textfield',
      '#description' => $this->t(''),
    ];
    $form["cml"]['zip'] = array(
      '#title' => $this->t('Allow zip compression'),
      '#type' => 'checkbox',
      '#maxlength' => 20,
      '#required' => FALSE,
      '#size' => 15,
      '#default_value' => $config->get('zip'),
    );
    $form['cml']['images_path'] = [
      '#title' => $this->t('Path for imported images'),
      '#default_value' => $config->get('images-path'),
      '#maxlength' => 20,
      '#required' => FALSE,
      '#size' => 15,
      '#type' => 'textfield',
      '#description' => $this->t('Путь относительно папки files'),
    ];
    $form['cml']['file_limit'] = [
      '#title' => $this->t('Filesize limit'),
      '#default_value' => $config->get('file-limit', 0),
      '#maxlength' => 20,
      '#required' => FALSE,
      '#size' => 15,
      '#type' => 'textfield',
    ];

    return parent::buildForm($form, $form_state);
  }



  /**
   * Implements form validation.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Implements a form submit handler.
   */
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('cmlservice.settings');
    $config
      ->set('auth'       , $form_state->getValue('auth'))
      ->set('debug'      , $form_state->getValue('debug'))
      ->set('auth-user'  , $form_state->getValue('auth_user'))
      ->set('auth-pass'  , $form_state->getValue('auth_pass'))
      ->set('zip'        , $form_state->getValue('zip'))
      ->set('images-path', $form_state->getValue('images_path'))
      ->set('file-limit' , $form_state->getValue('file_limit'))
      ->save();
  }
}
