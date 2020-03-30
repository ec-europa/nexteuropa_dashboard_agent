<?php

/**
 * @file
 * Contains \Drupal\pants\Controller\NextEuropaDashboardController.
 */

namespace Drupal\nexteuropa_dashboard_agent\Controller;

use Drupal;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\nexteuropa_dashboard_agent\Services\NextEuropaDashboardEncryption;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Returns responses for Sensei's Pants routes.
 */
class NextEuropaDashboardController extends ControllerBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $module_handler;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $theme_handler;

  /**
   * The NextEuropa Dashboard encrypt service
   *
   * @var \Drupal\nexteuropa_dashboard_agent\Services\NextEuropaDashboardEncryption
   */
  protected $encrypt;

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * NextEuropaDashboardController constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   * @param \Drupal\nexteuropa_dashboard_agent\Services\NextEuropaDashboardEncryption $encrypt
   * @param \Drupal\Core\Session\AccountProxy $current_user
   */
  public function __construct(ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, NextEuropaDashboardEncryption $encrypt, AccountProxy $current_user) {
    $this->module_handler = $module_handler;
    $this->theme_handler = $theme_handler;
    $this->encrypt = $encrypt;
    $this->currentUser = $current_user;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('theme_handler'),
      $container->get('nexteuropa_dashboard_agent.encrypt'),
      $container->get('current_user')
    );
  }

  /**
   * Changes Sensei's pants and returns the display of the new status.
   */
  function load() {
    $drupal_modules = system_rebuild_module_data();
    $drupal_themes = $this->theme_handler->listInfo();

    // Needless initialisation, but hey.
    $res = [
      'core' => [],
      'module' => [],
      'profile' => [],
      'theme' => [],
    ];

    // Go over modules.
    foreach ($drupal_modules as $name => $module) {

      // Match for installation profiles.
      if (strpos($module->getPathname(), '/profiles/') !== FALSE) {
        $res['profile'][$name] = [
          'name' => $module->info['name'],
          'version' => $module->info['version'],
          'status' => $module->status,
          'schema_version' => $module->schema_version,
          'location' => $module->getPathname(),
          'package' => $module->info['package'],
          'requires' => array_keys($module->requires),
        ];
        continue;
      }

      // Match for core modules.
      if ($module->info['package'] == 'Core'
        || $module->info['package'] == 'Field types'
        || $module->info['package'] == 'drupal'
        || $module->origin == 'core') {
        $res['core'][$name] = [
          'name' => $module->info['name'],
          'version' => $module->info['version'],
          'status' => $module->status,
          'schema_version' => $module->schema_version,
          'location' => $module->getPathname(),
          'package' => $module->info['package'],
          'requires' => array_keys($module->requires),
        ];
        continue;
      }

      // What now remains must be modules.
      $res['module'][$name] = [
        'name' => $module->info['name'],
        'version' => $module->info['version'],
        'status' => $module->status,
        'schema_version' => $module->schema_version,
        'location' => $module->getPathname(),
        'package' => $module->info['package'],
        'requires' => array_keys($module->requires),
      ];
    }

    // Go over themes.
    foreach ($drupal_themes as $name => $theme) {
      $res['theme'][$name] = [
        'name' => $theme->info['name'],
        'version' => isset($theme->info['version']) ? $theme->info['version'] : NULL,
        'status' => $theme->status,
        'location' => $theme->getPathname(),
        'base_themes' => !empty($theme->base_themes) ? array_keys($theme->base_themes) : [],
      ];
    }

    $platform_tag = array('platform_tag' => NULL);
    $platform_commit_number = array('platform_commit_number' => NULL);
    $platform_installation_time = array('platform_installation_time' => NULL);

    $manifest_file = $this->readManifestFile();
    if ($manifest_file !== FALSE) {
      $platform_tag = array('platform_tag' => $manifest_file['version']);
      $platform_commit_number = array('platform_commit_number' => $manifest_file['sha']);
    }

    $res = array_merge(
      $res,
      ['drupal_version' => 'D' . \DRUPAL::VERSION],
      $platform_tag,
      $platform_commit_number,
      $platform_installation_time,
      ['php_version' => phpversion()]);

    $use_encryption = $this->config('nexteuropa_dashboard_agent.settings')
      ->get('nexteuropa_dashboard_agent_use_encryption');
    if ($use_encryption && function_exists('openssl_random_pseudo_bytes')) {
      $res = NextEuropaDashboardEncryption::encrypt_openssl(json_encode(["nexteuropa_dashboard" => $res]));
      return new JsonResponse([
        "nexteuropa_dashboard" => "encrypted_openssl",
        "data" => $res,
      ]);
    }
    else {
      return new JsonResponse(["nexteuropa_dashboard" => $res]);
    }
  }

  /**
   * Read and returns the content of manifest.json file.
   */
  private function readManifestFile(){

    $filename = '../manifest.json';
    if (!file_exists($filename) || !is_readable($filename)) return FALSE;

    $file_content = file_get_contents($filename);
    if ($file_content === FALSE) return FALSE;

    return json_decode($file_content,TRUE );
  }

  /**
   * Returns a one time user login url for user id 1.
   *
   * @param $ne_dashboard_agent_req_for_user
   *
   * @return JsonResponse
   */
  function uli($ne_dashboard_agent_req_for_user = FALSE) {
    // The param $ne_dashboard_agent_req_for_user has been added
    // to simplify the dashboard implementation
    // but for D8 a one time login link for user id 1 is always returned.
    // See https://webgate.ec.europa.eu/CITnet/jira/browse/DASH-233
    $uid = 1;
    $account = User::load($uid);

    return new JsonResponse(user_pass_reset_url($account) . '/login');
  }

  /**
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
   */
  public function access() {

    if ($this->currentUser == 1) {
      // Always allow the access to user 1.
      return AccessResult::allowed();
    }

    $nexteuropa_dashboard_agent_token = \Drupal::request()->headers->get('netoken');
    if (!empty($nexteuropa_dashboard_agent_token)) {

      $provided_salt = mb_substr($_SERVER['HTTP_NETOKEN'], 0, 4);
      $provided_hash = mb_substr($_SERVER['HTTP_NETOKEN'], 4);
      $hash_of_temporary_token = NextEuropaDashboardEncryption::get_hash_of_temporary_token($provided_salt);
      if ($provided_hash == $hash_of_temporary_token) {

        $allowed_ips = $this->config('nexteuropa_dashboard_agent.settings')
          ->get('nexteuropa_dashboard_agent_allowed_ip_range');
        if (empty($allowed_ips)) {
          Drupal::logger('nexteuropa_dashboard_agent')
            ->warning("Variable with allowed IP range isn't set. Request blocked.");
          return AccessResult::forbidden();
        }
        else {
          $allowed_ips = preg_replace('/\s+/', '', $allowed_ips);
          $allowed_ip_list = explode(',', $allowed_ips);
          // Compare current IP with the list of allowed IPs.
          foreach ($allowed_ip_list as $allowed_ip) {
            $allowed_ip_array = explode('-', $allowed_ip);
            list($lower, $upper) = $allowed_ip_array;
            $lower_dec = (float) sprintf("%u", ip2long($lower));
            $upper_dec = (!empty($upper)) ? (float) sprintf("%u", ip2long($upper)) : $lower_dec;
            $ip_dec = (float) sprintf("%u", ip2long(Drupal::request()
              ->getClientIp()));
            if (($ip_dec >= $lower_dec) && ($ip_dec <= $upper_dec)) {
              // Current IP is in the list of allowed IPs.
              // Allow the access.
              return AccessResult::allowed();
            }
          }
          // If the execution reach this line,
          // this means current IP is not allowed.
          Drupal::logger('nexteuropa_dashboard_agent')
            ->warning("Requester IP *** %requester_ip *** is not allowed. Should be in the list *** %allowed_ips ***.",
              [
                '%requester_ip' => Drupal::request()->getClientIp(),
                '%allowed_ips' => $allowed_ips,
              ]);
          return AccessResult::forbidden();
        }
      }
      else {
        Drupal::logger('nexteuropa_dashboard_agent')
          ->warning("Provided hash *** %provided_hash *** doesn't match the generated one.",
            ['%provided_hash' => $provided_hash]);
        return AccessResult::forbidden();
      }
    }
    else {
      Drupal::logger('nexteuropa_dashboard_agent')
        ->warning("No access token provided.", []);
      return AccessResult::forbidden();
    }
  }

}
