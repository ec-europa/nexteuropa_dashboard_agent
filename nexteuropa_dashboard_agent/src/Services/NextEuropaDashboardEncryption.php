<?php

/**
 * @file
 * Encryption logic for nexteuropa_dashboard_agent
 */

namespace Drupal\nexteuropa_dashboard_agent\Services;

use Drupal;

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
    $encrypt_token = Drupal::config('\nexteuropa_dashboard_agent.settings')->get('\nexteuropa_dashboard_agent_encrypt_token');
    $key = hash("SHA256", $encrypt_token, TRUE);
    $plaintext_utf8 = utf8_encode($plaintext);

    $iv = openssl_random_pseudo_bytes(16);
    $cyphertext = openssl_encrypt($plaintext_utf8, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

    return base64_encode($iv.$cyphertext);
  }
}
