<?php

/**
 * @file
 * Encryption logic for nexteuropa_dashboard_agent
 */

namespace Drupal\nexteuropa_dashboard_agent\Services;

use Drupal;
use Drupal\Core\File\FileSystemInterface;

class NextEuropaDashboardEncryption {

  /**
   * NextEuropa Dashboard: create a new token.
   */
  public static function getToken() {
    $chars = array_merge(range(0, 9),
      range('a', 'z'),
      range('A', 'Z'),
      range(0, 99));

    shuffle($chars);

    $token = "";
    for ($i = 0; $i < 8; $i++) {
      $token .= $chars[$i];
    }

    return $token;
  }

  /**
   * NextEuropa Dashboard: encrypt a plaintext message using openssl.
   *
   * @param $plaintext
   *
   * @return string
   */
  public static function encrypt_openssl($plaintext) {
    $encrypt_token = NextEuropaDashboardEncryption::get_token('nexteuropa_dashboard_agent_encrypt_token');
    $key = hash("SHA256", $encrypt_token, TRUE);
    $plaintext_utf8 = utf8_encode($plaintext);

    $iv = openssl_random_pseudo_bytes(16);
    $cyphertext = openssl_encrypt($plaintext_utf8, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

    return base64_encode($iv.$cyphertext);
  }

  /**
   * Stores a given token in private file directory.
   *
   * @param string $name
   *   The name of the token.
   * @param string $token
   *   The token to be stored.
   */
  public static function set_token($name, $token) {
    \Drupal::service('file_system')->saveData($token, 'private://' . $name, FileSystemInterface::EXISTS_REPLACE);
  }

  /**
   * Retrieves a token stored in private file directory.
   *
   * @param string $name
   *   The name of the token.
   *
   * @return string
   *   'Error-no-token' when the token doesn't exist.
   */
  public static function get_token($name) {
    $real_path = \Drupal::service('file_system')->realpath('private://' . $name);
    $token = file_get_contents($real_path);

    if ($token !== FALSE) {
      return $token;
    }
    else {
      return 'Error-no-token';
    }
  }

  /**
   * Deletes a token stored in private file directory.
   *
   * @param string $name
   *   The name of the token.
   */
  public static function remove_token($name) {
    \Drupal::service('file_system')->delete('private://' . $name);
  }

}
