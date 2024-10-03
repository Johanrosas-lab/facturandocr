<?php

namespace Drupal\osc_commerce_extra;
use Drupal\Core\Entity\Entity as Entity;

/**
 * Interface pdftkOscInterface.
 */
interface pdftkOscInterface {

  public function fillPdfForm(Entity $pdf, array $options, array $data);

}
