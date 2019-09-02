<?php

/**
 * @file
 * Contains \Drupal\pants\Form\PantsSettingsForm.
 */

namespace Drupal\nexteuropa_dashboard_agent\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

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
      '#description' => $config->get('nexteuropa_dashboard_agent_token') . "-" . $config->get('nexteuropa_dashboard_agent_encrypt_token'),
      '#default_value' => $config->get('nexteuropa_dashboard_agent_token') . "-" . $config->get('nexteuropa_dashboard_agent_encrypt_token'),
      '#attributes' => array('style' => array('display:none;')),
      '#size' => 60,
      '#maxlength' => 60,
      '#disabled' => TRUE,
    );

    $form['nexteuropa_dashboard_agent_use_encryption'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use encryption'),
      '#description' => t('Encrypt the data before sending this back to the requester.'),
      '#default_value' => $config->get('nexteuropa_dashboard_agent_use_encryption'),
    );

    $form['nexteuropa_dashboard_agent_allowed_ip_range'] = array(
      '#type' => 'textfield',
      '#title' => t('Range of IP allowed to request the services'),
      '#description' => t('Defines the allowed IPs from where the request can be made.<br>If the variable is empty, then all IPs are allowed.<br>IP-address range is entered in the form of 100.100.100.100 - 100.100.101.150.'),
      '#default_value' => $config->get('nexteuropa_dashboard_agent_allowed_ip_range'),
      '#attributes' => array(
        'placeholder' => t('100.100.100.100 - 100.100.101.150'),
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
    $this->config('nexteuropa_dashboard_agent.settings')
      ->set('nexteuropa_dashboard_agent_use_encryption', $form_state->getValue('nexteuropa_dashboard_agent_use_encryption'))
      ->set('nexteuropa_dashboard_agent_allowed_ip_range', $form_state->getValue('nexteuropa_dashboard_agent_allowed_ip_range'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
