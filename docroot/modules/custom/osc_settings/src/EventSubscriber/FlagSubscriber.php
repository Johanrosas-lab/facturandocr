<?php

/**
* @file
* Contains \Drupal\osc_settings\EventSubscriber\FlagSubscriber.
*/

namespace Drupal\osc_settings\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\flag\Event\FlagEvents;
use Drupal\flag\Event\FlaggingEvent;
use Drupal\flag\Event\UnflaggingEvent;

class FlagSubscriber implements EventSubscriberInterface {

 public function onFlag(FlaggingEvent $event) {

 	//sends an email when a user accepts the informed consent
	 $mailManager = \Drupal::service('plugin.manager.mail');
	 $module = 'osc_settings';
	 $key = 'informed_consent_email';
	 $to = \Drupal::currentUser()->getEmail(); 
	 $params['message'] = 'Usted ha aceptado los términos y condiciones de AsegúreseCR';
	 $langcode = \Drupal::currentUser()->getPreferredLangcode();
	 $send = true;
	 $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
	 if ($result['result'] !== true) {
	 	$messenger = \Drupal::messenger();
		$messenger->addMessage(t('There was a problem sending your message and it was not sent.'), $messenger::TYPE_ERROR);
	 }
	 else {
	 	$messenger = \Drupal::messenger();
		$messenger->addMessage(t('Your message has been sent.'));
	}

    $flagging = $event->getFlagging();
    $entity_nid = $flagging->getFlaggable()->id();
   }

  public function onUnflag(UnflaggingEvent $event) {
    $flagging = $event->getFlaggings();
    $flagging = reset($flagging);
    $entity_nid = $flagging->getFlaggable()->id();
   }

public static function getSubscribedEvents() {
  $events = [];
  $events[FlagEvents::ENTITY_FLAGGED][] = ['onFlag'];
  $events[FlagEvents::ENTITY_UNFLAGGED][] = ['onUnflag'];
  return $events;
}

}