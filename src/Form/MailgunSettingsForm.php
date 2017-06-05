<?php

namespace Drupal\mailgun\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class MailgunSettingsForm.
 *
 * @package Drupal\mailgun\Form
 */
class MailgunSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'mailgun.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mailgun_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('mailgun.settings');

    $url = Url::fromUri('https://mailgun.com/app/domains');
    $link = \Drupal::l(t('mailgun.com/app/domains'), $url);

    $form['description'] = [
      '#markup' => "Please refer to $link for your settings."
    ];

    $form['api_key'] = [
      '#title' => t('Mailgun API Key'),
      '#type' => 'textfield',
      '#description' => t('Enter your API key.'),
      '#default_value' => $config->get('api_key'),
    ];

    $form['api_endpoint'] = [
      '#title' => t('Mailgun API Endpoint'),
      '#type' => 'textfield',
      '#description' => t('Enter your API endpoint.'),
      '#default_value' => $config->get('api_endpoint'),
    ];

    $form['working_domain'] = [
      '#title' => t('Mailgun API Working Domain'),
      '#type' => 'textfield',
      '#description' => t('Enter your API working domain.'),
      '#default_value' => $config->get('working_domain'),
    ];

    $form['debug_mode'] = [
      '#title' => t('Enable Debug Mode'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('debug_mode'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config('mailgun.settings')
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('api_endpoint', $form_state->getValue('api_endpoint'))
      ->set('working_domain', $form_state->getValue('working_domain'))
      ->set('debug_mode', $form_state->getValue('debug_mode'))
      ->save();

    drupal_set_message($this->t('The configuration options have been saved.'));
  }

}
