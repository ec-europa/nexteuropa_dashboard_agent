<?php

/**
 * @file
 * Logic for nexteuropa_dashboard_agent one time user login.
 */

define('NEXTEUROPA_DASHBOARD_AGENT_UID_1', 'super_admin');
define('NEXTEUROPA_DASHBOARD_AGENT_UID_ADMIN', 'user_administrator');

/**
 * Returns a one time user login url for user id 1.
 */
function nexteuropa_dashboard_agent_uli_page($requested_for_user = FALSE) {
  if ($requested_for_user !== FALSE) {

    switch ($requested_for_user) {
      case NEXTEUROPA_DASHBOARD_AGENT_UID_1:
        $uid = 1;
        $account = user_load($uid);
        drupal_json_output(user_pass_reset_url($account) . '/login');
        break;

      case NEXTEUROPA_DASHBOARD_AGENT_UID_ADMIN:
        $account = user_load_by_name(NEXTEUROPA_DASHBOARD_AGENT_UID_ADMIN);
        if ($account !== FALSE) {
          drupal_json_output(user_pass_reset_url($account) . '/login');
        }
        elseif (module_exists('cce_basic_config') === FALSE) {
          $uid = 1;
          $account = user_load($uid);
          drupal_json_output(user_pass_reset_url($account) . '/login');
        }
        else {
          drupal_json_output('User ' . NEXTEUROPA_DASHBOARD_AGENT_UID_ADMIN . ' does not exist.');
        }
        break;

      default:
        drupal_json_output('Unknown user.');
        break;
    }
  }
  else {
    drupal_json_output('No user specified.');
  }

}
