<?php

/**
 * @file
 * Contains \Drupal\pants\Form\PantsSettingsForm.
 */

namespace Drupal\nexteuropa_dashboard_agent\Form;

use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\nexteuropa_dashboard_agent\Services\NextEuropaDashboardEncryption;

/**
 * Configure pants settings for this site.
 */
class NextEuropaDashboardSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'nexteuropa_dashboard_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('nexteuropa_dashboard_agent.settings');

    $form['nexteuropa_dashboard_service'] = array(
      '#type' => 'textfield',
      '#title' => t('Your siteUUID'),
      '#description' => NextEuropaDashboardEncryption::get_token('nexteuropa_dashboard_agent_token') . "-" . NextEuropaDashboardEncryption::get_token('nexteuropa_dashboard_agent_encrypt_token'),
      '#default_value' => NextEuropaDashboardEncryption::get_token('nexteuropa_dashboard_agent_token') . "-" . NextEuropaDashboardEncryption::get_token('nexteuropa_dashboard_agent_encrypt_token'),
      '#attributes' => array('style' => array('display:none;')),
      '#size' => 60,
      '#maxlength' => 60,
      '#disabled' => TRUE,
    );
    $form['update_tokens'] = array(
      '#type' => 'submit',
      '#value' => t('Update tokens'),
      '#name' => 'update_tokens',
    );
    //$form['#submit'][] = '_nexteuropa_dashboard_agent_update_tokens';

    $form['nexteuropa_dashboard_agent_use_encryption'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use encryption'),
      '#description' => t('Encrypt the data before sending this back to the requester.'),
      '#default_value' => $config->get('nexteuropa_dashboard_agent_use_encryption'),
    );

    $form['nexteuropa_dashboard_agent_allowed_ip_range'] = array(
      '#type' => 'textfield',
      '#title' => t('List of IPs allowed to request the services'),
      '#description' => t('Defines the allowed IPs from where the request can be made.<br>If the variable is empty, then all requests are blocked.<br>IP-address range is entered in the form of 100.100.100.100 - 100.100.101.150, 158.167.213.96, 158.167.217.43, 158.167.315.02 - 158.167.315.80'),
      '#size' => 180,
      '#maxlength' => 200,
      '#default_value' => $config->get('nexteuropa_dashboard_agent_allowed_ip_range'),
      '#attributes' => array(
        'placeholder' => t('100.100.100.100 - 100.100.101.150, 158.167.213.96, 158.167.217.43, 158.167.315.02 - 158.167.315.80'),
      ),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['nexteuropa_dashboard_agent.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#name'] == 'update_tokens') {
      $encrypt = Drupal::service('nexteuropa_dashboard_agent.encrypt');
      NextEuropaDashboardEncryption::set_token('nexteuropa_dashboard_agent_token', $encrypt::getToken());
      NextEuropaDashboardEncryption::set_token('nexteuropa_dashboard_agent_encrypt_token', $encrypt::getToken());
    }

    $this->config('nexteuropa_dashboard_agent.settings')
      ->set('nexteuropa_dashboard_agent_use_encryption', $form_state->getValue('nexteuropa_dashboard_agent_use_encryption'))
      ->set('nexteuropa_dashboard_agent_allowed_ip_range', $form_state->getValue('nexteuropa_dashboard_agent_allowed_ip_range'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
