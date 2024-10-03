<?php

namespace Drupal\osc_settings\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides a 'Powered By' Block.
 *
 * @Block(
 *   id = "powered_by_osc_block",
 *   admin_label = @Translation("Powered By"),
 *   category = @Translation("Powered By"),
 * )
 */

class PoweredByBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */

  public function build() {
  	$url6 = Url::fromUri('https://outerspacecoders.com');
	$link1 = Link::fromTextAndUrl(t('Outer Space Coders'), $url6);
    return array(
      '#markup' => $this->t('© Copyright %year Powered by %osc', ['%year'=> date('Y'), '%osc' => $link1->toString()]),
    );
  }

  public function getCacheMaxAge() {
    return 0;
  }

}
