<?php


namespace Drupal\ins_payment\Services;

use Drupal\new_dependency_test\Service;

/**
 * Prepare Data with encryption
 *
 * Class InsPaymentPrepareData
 *
 * @package Drupal\ins_payment\Services
 */
class InsPaymentPrepareData {

  /**
   * Prepare data to encrypt or decrypt values.
   *
   * @param $data
   * @param string $type
   *
   * @return mixed
   */
  public function prepareEncryptedData($data, $config) {
    $value_manipulated = NULL;
    // Verify exist pem file
    if (isset($config['format_pem']) && !empty($config['format_pem'])) {
      // Get pem key.
      $gem_key_file = $config['format_pem'];
      foreach ($data as &$value) {
          // Encrypt all data from the array.
        $encrypted = openssl_public_encrypt(
          $value,
          $value_manipulated,
          $gem_key_file,
          OPENSSL_PKCS1_PADDING
        );
        $value = base64_encode($value_manipulated);
      }
    }
    return $data;
  }

}
