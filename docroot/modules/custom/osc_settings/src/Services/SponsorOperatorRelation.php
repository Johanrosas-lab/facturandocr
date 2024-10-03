<?php
namespace Drupal\osc_settings\Services;

  class SponsorOperatorRelation {

    /**
     * Get the operator from the sponsor.
     * @param string|integer $uid
     *    The uid from sponsor.
     * @return string|null
     */
    public function getSponsorData($uid) {
      $connection = \Drupal::database();
      $query = $connection->query("SELECT p.profile_id, p.uid FROM 
            `profile` AS p LEFT JOIN `profile__field_sponsor` AS ps 
            ON p.profile_id = ps.entity_id WHERE ps.field_sponsor_target_id = :uid",
        [
          ':uid' => $uid,
        ]);
      if ($operator = $query->fetchAssoc()) {
        return (isset($operator['uid']) && $operator['uid']) ? $operator['uid']
          : NULL;
      }
      return NULL;
    }

    /**
     * Get the operator name.
     * @param string|integer $uid
     *    The uid from operator.
     * @return string|null
     */
    public function getOperatorName($uid) {
      $connection = \Drupal::database();
      $query = $connection->query("SELECT pn.field_nombre_value FROM 
        `profile__field_nombre` AS pn LEFT JOIN `profile` AS p ON 
        pn.entity_id = p.profile_id WHERE p.uid = :uid AND p.type = 'operador'",
        [
          ':uid' => $uid,
        ]
      );
      if ($operator = $query->fetchAssoc()) {
        return (isset($operator['field_nombre_value'])) ?
          $operator['field_nombre_value'] : NULL;
      }
      return NULL;
    }

    /**
     * Get the sponsor name.
     * @param string|integer $uid
     *    The uid from sponsor.
     * @return string|null
     */
    public function getSponsorName($uid) {

      $connection = \Drupal::database();
      $query = $connection->query("SELECT pn.field_nombre_sponsor_value FROM 
        `profile__field_nombre_sponsor` AS pn LEFT JOIN `profile` AS p ON 
        pn.entity_id = p.profile_id WHERE p.uid = :uid AND p.type = 'sponsor'",
        [
          ':uid' => $uid,
        ]
      );
      if ($operator = $query->fetchAssoc()) {
        return (isset($operator['field_nombre_sponsor_value'])) ?
          $operator['field_nombre_sponsor_value'] : NULL;
      }
      return NULL;
    }
  }
