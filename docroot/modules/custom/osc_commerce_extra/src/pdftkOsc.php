<?php

namespace Drupal\osc_commerce_extra;

use Drupal\Core\Entity\Entity;
use mikehaertl\pdftk\Pdf;

/**
 * Class pdftkOsc.
 */
class pdftkOsc implements pdftkOscInterface {

  /**
   * Constructs a new pdftkOsc object.
   */
  public function __construct() {

  }

  /**
   * @param \Drupal\Core\Entity\Entity $entity Drupal entity that has a PDF
   * @param array $data
   * @param array $options
   *
   * @return bool|\mikehaertl\pdftk\Pdf
   */
  public function fillPdfForm(Entity $entity, $options, $data) {
    // Create new destination for the PDF, it will be a field in the actual order (one for each product that was purchased
    $pdf = new Pdf($options['path']);

    $pdf->fillForm($data)
      ->needAppearances()
      ->flatten();

    if (!$pdf->saveAs($options['path'])) {
      \Drupal::messenger()->addError(t('No se pudo creear el PDF'));
      return false;
    } else {
      return $pdf;
    }
  }
}
