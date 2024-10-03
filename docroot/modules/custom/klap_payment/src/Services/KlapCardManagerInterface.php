<?php

namespace Drupal\klap_payment\Services;

/**
 * Interface KlapCardManagerInterface.
 */
interface KlapCardManagerInterface {

  /**
   * User Request Cards.
   *
   */
  function getUserCard();

  /**
   * User Inlcude a new Cards.
   */
  function addUserCard($dataCardObject);

  /**
   * User Set Default Card.
   */
  function addFavoriteCard($dataCardObject);

  function applyPayment();

  function UserDeleteCard($card);

}
