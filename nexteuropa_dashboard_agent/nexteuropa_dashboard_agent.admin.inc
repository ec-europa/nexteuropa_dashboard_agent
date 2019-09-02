<?php

/**
 * @file
 * Admin forms for nexteuropa_dashboard_agent.
 */

/**
 * Page callback: NextEuropa Dashboard Agent settings.
 *
 * @see nexteuropa_dashboard_agent_menu()
 */
function nexteuropa_dashboard_agent_form($form, &$form_state) {

  $form['nexteuropa_dashboard_agent_service'] = array(
    '#type' => 'textfield',
    '#title' => t('Your siteUUID'),
    '#description' => nexteuropa_dashboard_agent_get_site_uu_id(),
    '#default_value' => nexteuropa_dashboard_agent_get_site_uu_id(),
    '#attributes' => array('style' => array('display:none;')),
    '#size' => 60,
    '#maxlength' => 60,
    '#disabled' => TRUE,
  );

  $form['nexteuropa_dashboard_agent_use_encryption'] = array(
    '#type' => 'checkbox',
    '#title' => t('Use encryption'),
    '#description' => t('Encrypt the data before sending this back to the requester.'),
    '#default_value' => variable_get('nexteuropa_dashboard_agent_use_encryption', FALSE),
  );

  $form['nexteuropa_dashboard_agent_allowed_ip_range'] = array(
    '#type' => 'textfield',
    '#title' => t('Range of IP allowed to request the services'),
    '#description' => t('Defines the allowed IPs from where the request can be made.<br>If the variable is empty, then all IPs are allowed.<br>IP-address range is entered in the form of 100.100.100.100 - 100.100.101.150.'),
    '#default_value' => variable_get('nexteuropa_dashboard_agent_allowed_ip_range'),
    '#attributes' => array(
      'placeholder' => t('100.100.100.100 - 100.100.101.150'),
    ),
  );

  return system_settings_form($form);
}

/**
 * Retrieve the site UUID.
 *
 * @return string
 *   The site UUID.
 */
function nexteuropa_dashboard_agent_get_site_uu_id() {
  $siteUUID = variable_get('nexteuropa_dashboard_agent_token', 'Error-no-token');
  $siteUUID .= "-";
  $siteUUID .= variable_get('nexteuropa_dashboard_agent_encrypt_token', 'Error-no-token');

  return $siteUUID;
}
