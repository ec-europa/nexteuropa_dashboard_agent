<?php

/**
 * @file
 * Logic for nexteuropa_dashboard_agent one time user login.
 */

/**
 * Returns a one time user login url for user id 1.
 */
function nexteuropa_dashboard_agent_uli_page() {
  $uid = 1;
  $account = user_load($uid);

  return user_pass_reset_url($account) . '/login';
}
