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
   * NextEuropaDashboardController constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   * @param \Drupal\nexteuropa_dashboard_agent\Services\NextEuropaDashboardEncryption $encrypt
   */
  public function __construct(ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, NextEuropaDashboardEncryption $encrypt) {
    $this->module_handler = $module_handler;
    $this->theme_handler = $theme_handler;
    $this->encrypt = $encrypt;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('theme_handler'),
      $container->get('nexteuropa_dashboard_agent.encrypt')
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
      ];
    }

    $res = array_merge(
      $res,
      ['drupal_version' => 'D' . \DRUPAL::VERSION],
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
   * Returns a one time user login url for user id 1.
   */
  function uli() {
    $uid = 1;
    $account = User::load($uid);

    return new JsonResponse(user_pass_reset_url($account) . '/login');
  }

  /**
   * @param $nexteuropa_dashboard_agent_token
   *
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
   */
  public function access($nexteuropa_dashboard_agent_token) {
    $token = NextEuropaDashboardEncryption::get_token('nexteuropa_dashboard_agent_token');
    if ($token == $nexteuropa_dashboard_agent_token) {

      $allowed_ips = $this->config('nexteuropa_dashboard_agent.settings')
        ->get('nexteuropa_dashboard_agent_allowed_ip_range');
      if (empty($allowed_ips)) {
        Drupal::logger('nexteuropa_dashboard_agent')
          ->warning("Variable with allowed IP range isn't set. Request blocked.");
        return AccessResult::forbidden();
      }
      else {
        $allowed_ips = preg_replace('/\s+/', '', $allowed_ips);
        $allowed_ip_array = explode('-', $allowed_ips);
        list($lower, $upper) = $allowed_ip_array;
        $lower_dec = (float) sprintf("%u", ip2long($lower));
        $upper_dec = (float) sprintf("%u", ip2long($upper));
        $ip_dec = (float) sprintf("%u", ip2long(Drupal::request()
          ->getClientIp()));
        if (($ip_dec >= $lower_dec) && ($ip_dec <= $upper_dec)) {
          return AccessResult::allowed();
        }
        else {
          Drupal::logger('nexteuropa_dashboard_agent')
            ->warning("Requester IP *** %requester_ip *** is not allowed. Should be in the range *** %allowed_ips ***.",
              [
                '%requester_ip' => Drupal::request()->getClientIp(),
                '%allowed_ips' => $allowed_ips,
              ]);
          return AccessResult::forbidden();
        }
      }
    }
    else {
      Drupal::logger('nexteuropa_dashboard_agent')
        ->warning("Provided token *** %provided_token *** doesn't match the defined one.",
          ['%provided_token' => $nexteuropa_dashboard_agent_token]);
      return AccessResult::forbidden();
    }
  }

}
