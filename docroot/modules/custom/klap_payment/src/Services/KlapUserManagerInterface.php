<?php

namespace Drupal\klap_payment\Services;

/**
 * Interface KlapManagerInterface.
 */
interface KlapUserManagerInterface {

  /**
   * Create new user on platform.
   *
   * @param $current_user
   *
   * @return mixed
   */
  function createUser($profile_object);

  function logOnUser();

  function userDelete($user);
  
  function logOnApp();

}