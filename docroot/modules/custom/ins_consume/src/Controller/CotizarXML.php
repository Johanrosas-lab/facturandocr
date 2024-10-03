<?php
/**
 * @file
 * Contains \Drupal\ins_consume\Controller\CotizarXML.
 */

namespace Drupal\ins_consume\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;


class CotizarXML extends ControllerBase {
  public function cotizar() {
    // Create Basic elements
    $primas = [];
    $coberturas = [];
    $basic_information = new \stdClass();
    if(isset($_SESSION['ins-cot'])){
      // convertingc to XML
      $response  = (simplexml_load_string($_SESSION['ins-cot'])->InfoRespuesta);
      if($response->CodEstado != '00'){
        return  [
          '#theme' => 'error_ins',
        ];
      }
      // Get the data
      $this->clean_data($response, $basic_information, $coberturas, $primas);
    }
    return  [
      '#theme' => 'cotizar_xml',
      '#primas' => $primas,
      '#coberturas' => $coberturas,
      '#basic_information' => $basic_information
    ];
  }
  public function comprar() {
    try {
      if (isset($_SESSION['ins-cot'])) {
        //  Define all atributes to use in the cart.
        $store_id = 1;
        $order_type = 'default'; // If you have several order types, specify one here.
        $quotation  = (simplexml_load_string($_SESSION['ins-cot'])->InfoRespuesta);
        $variation = $this->create_variation_product($quotation);
        $entity_manager = \Drupal::service('entity.manager');
        $cart_manager = \Drupal::service('commerce_cart.cart_manager');
        $cart_provider = \Drupal::service('commerce_cart.cart_provider');
        $store = $entity_manager->getStorage('commerce_store')->load($store_id);

        $cart = $cart_provider->getCart($order_type, $store);
        // Create the cart if it has'nt exist
        if (!$cart) {
          $cart = $cart_provider->createCart($order_type, $store);
        }
        $order_item = $entity_manager->getStorage('commerce_order_item')->create(array(
          'type' => 'default',
          'purchased_entity' => (string) $variation->getOriginalId(),
          'quantity' => 1,
          'unit_price' => $variation->getPrice(),
        ));

        $order_item->save();

        $cart_manager->addOrderItem($cart, $order_item);

        // Delete session to prevent doble add to cart item
        unset($_SESSION['ins-cot']);
        // Redirect to cart page
        $response = new RedirectResponse(Url::fromRoute('commerce_cart.page')->toString());
        return $response;
      }

    } catch (Exception $exc) {
      \Drupal::logger('Ins error')->notice($this->t("Error trying to add created a product: ").$exc->getTraceAsString());
    }
    return['#markup' => $this->t("Impossible to add this product to the cart try later")];
  }
  public function create_variation_product($quotation) {
    // The price of the variation.
    $price = new \Drupal\commerce_price\Price((string) $quotation->Primas->Prima->PrimaPago, 'USD');
    $variation = \Drupal\commerce_product\Entity\ProductVariation::create([
      'type' => 'seguro', // The default variation type is 'default'.
      'sku' => 'test-product-01-1', // The variation sku.
      'status' => 1, // The product status. 0 for disabled, 1 for enabled.
      'price' => $price,
    ]);
    $variation->save();
    return $variation;
  }

  /**
   * Clean the response data
   * @param type $response
   * @param SimpleXMLElement $basic_information
   * @param array $coberturas
   * @param array $primas
   */
  public function clean_data($response,&$basic_information, &$coberturas, &$primas){
    //Get the basic information of the request
    $basic_information->CodEstado = (string) $response->CodEstado;
    $basic_information->NumCotizacion = (string) $response->NumCotizacion;
    $basic_information->Moneda = (string) $response->Moneda;
    $basic_information->Observaciones = (string) $response->Observaciones;
    // Get the report data
    if(isset($response->DetalleCotizacion) && is_object($response->DetalleCotizacion)) {
      if(isset($response->DetalleCotizacion->REPORTE)
        && is_object($response->DetalleCotizacion->REPORTE)){
        $basic_information->cotizacion = [];
        foreach ($response->DetalleCotizacion->REPORTE->COTIZACIONDATOS as $element) {
          $basic_information->cotizacion[] = ['campo' => (string) $element->CAMPO, 'valor' =>  (string) $element->VALOR];
        }
      }
      // Get all "coberturas"
      if(isset($response->DetalleCotizacion->COBERTURAS)
        && is_object($response->DetalleCotizacion->COBERTURAS)){
        foreach($response->DetalleCotizacion->COBERTURAS->COBERTURA as $element) {
          $coberturas[] = array(
            'nombre' => (string) $element->NOMBRE,
            'codigo' => (string) $element->CODIGO,
            'monto' => (string) $element->MONTO,
            'prima' => (string) $element->MONTOPRIMA,
          );
        }
      }
    }
    // Get all "primas"
    if(isset($response->Primas) && is_object($response->Primas)){
      $primas = $response->Primas->Prima;
    }
  }
}
