<?php

/**
 * @file
 * Contains \Drush\Commands\NextEuropaDashboardAgentCommands.
 */

namespace Drupal\nexteuropa_dashboard_agent\Commands;

use Drupal;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * For commands that are parts of modules, Drush expects to find commandfiles in
 * __MODULE__/src/Commands, and the namespace is Drupal/__MODULE__/Commands.
 *
 * In addition to a commandfile like this one, you need to add a drush.services.yml
 * in root of your module like this module does.
 */
class NextEuropaDashboardAgentCommands extends DrushCommands {

  /**
   * Generate new tokens.
   *
   * @command nexteuropa_dashboard_agent:ne-dashboard-agent-update-tokens
   * @aliases nedut
   * @usage drush ne-dashboard-agent-update-tokens
   *   Generate new tokens.
   */
  public function ne_dashboard_agent_update_tokens() {
    $encrypt = Drupal::service('nexteuropa_dashboard_agent.encrypt');
    $config = Drupal::configFactory()->getEditable('nexteuropa_dashboard_agent.settings');
    $config
      ->set('nexteuropa_dashboard_agent_token', $encrypt::getToken())
      ->set('nexteuropa_dashboard_agent_encrypt_token', $encrypt::getToken())
      ->save();

    return
      'New siteUUID is: '
      . $config->get('nexteuropa_dashboard_agent_token')
      . "-"
      . $config->get('nexteuropa_dashboard_agent_encrypt_token');
  }

}