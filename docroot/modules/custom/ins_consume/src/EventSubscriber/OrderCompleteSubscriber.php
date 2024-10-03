<?php

namespace Drupal\ins_consume\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client;

/**
 * Class OrderCompleteSubscriber.
 *
 * @package Drupal\ins_consume
 */
class OrderCompleteSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events['commerce_order.place.post_transition'] = ['orderCompleteHandler'];

    return $events;
  }

  /**
   * This method is called whenever the  commerce_order.place.post_transition event is
   * dispatched.
   *
   * @param WorkflowTransitionEvent $event
   */
  public function orderCompleteHandler(WorkflowTransitionEvent $event) {
    $values = $_SESSION['values'];
    $config = \Drupal::config('ins_consume.adminsettings');
    $myXMLData =
      '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cot="'.$config->get('default_url').'" xmlns:tran="'.$config->get('default_url').'">
      <soapenv:Header>
        <cot:SeguridadHeaderElement>
          <Empresa>'.$config->get('default_company').'</Empresa>
          <Sistema>'.$config->get('default_system').'</Sistema>
          <Direcciones>'.$config->get('default_address').'</Direcciones>
        </cot:SeguridadHeaderElement>
      </soapenv:Header>
      <soapenv:Body>
        <cot:EmisionClienteRequest>
          <InfoSolicitud>
            <InfoTransaccion>
              <FechaHora>'.date("Y-m-d")."T".date("H:i:s").'</FechaHora>
            </InfoTransaccion>
            <Emision>
              <ConConfiguracion>943</ConConfiguracion>
              <Solicitante>'.$config->get('default_user_information').'</Solicitante>
              <Parametros><![CDATA[
                <PARAMETROS>
                  <TIPOIDCLI>'.$values['tipo_de_identificacion'].'</TIPOIDCLI>
                  <IDENTCLI>'.$values['identificacion'].'</IDENTCLI>
                  <PRIAPELLIDOCLI/>
                  <SEGAPELLIDOCLI/>
                  <PRINOMBRECLI/>
                  <SEGNOMBRECLI/>
                  <NOMCOMPLETOCLI>'.$values['nombre_del_asegurado'].'</NOMCOMPLETOCLI>
                  <GENEROCLI/>
                  <FECNACICLI>'.$values['fecha_de_nacimiento'].'</FECNACICLI>
                  <TIPOTELCLI/>
                  <NUMTELCLI/>
                  <PROVCLI/>
                  <CANTCLI/>
                  <DISTCLI/>
                  <CORREOE>'.$values['correo_electronico'].'</CORREOE>
                  <VIGDESDE>'.$values['_cuando_iniciara_su_viaje_'].'</VIGDESDE>
                  <VIGHASTA>'.$values['_cuando_finalizara_su_viaje_'].'</VIGHASTA>
                  <NUMPOLIZA/>
                  <SUCEMI>'.$values['sucursal_emision'].'</SUCEMI>
                  <AGENTE>1105370</AGENTE>
                  <FORMAPAGO/>
                  <DIRECRSGO/>
                  <MONTO2>0</MONTO2>
                  <NUMOPERACION/>
                  <OBSERVACIONES>'.$values['destino_de_su_viaje'].'</OBSERVACIONES>
                  <MONTO1></MONTO1>
                  <CBEDAD>'.$values['edad'].'</CBEDAD>
                  <TIPOACTRSGO>'.$values['tipo_de_actividad'].'</TIPOACTRSGO>
                  <MOASR>NO</MOASR>
                  <TIPORIESGOCOB>'.$values['tipo_de_riesgo'].'</TIPORIESGOCOB>
                  <TIPOTARIFACOB>'.$values['tipo_tarifa'].'</TIPOTARIFACOB>
                  <SUMASEGRSGO>1</SUMASEGRSGO>
                  <PHOSINBUC>'.$values['numero_celular_o_telefono_fijo'].'</PHOSINBUC>
                  <DIRECSINBUC/>
                  <DIASVIAJE>0</DIASVIAJE>
                  <PROVSINBUC/>
                  <TIPOIDBENEF/>
                  <TIPOIDBENEF>'.$values['tipo_identificacion_beneficiario_1'].'</TIPOIDBENEF>
                  <IDBENEF>'.$values['no_identificacion_beneficiario'].'</IDBENEF>
                  <IDBENEF/>
                  <NOMCOMPBENEF>'.$values['nombre_del_beneficiario'].'</NOMCOMPBENEF>
                  <PARENTESCOBENEF>'.$values['parentesco'].'</PARENTESCOBENEF>
                  <PORCENTAJEBENEF>'.$values['porcentaje'].'</PORCENTAJEBENEF>
                  <NOMBENEF/>
                  <PRIAPEBENEF/>
                  <SEGAPEBENEF/>
                  <TIPOIDBENEF/>
                  <TIPOIDBENEF>0</TIPOIDBENEF>
                  <IDBENEF>0</IDBENEF>
                  <IDBENEF/>
                  <NOMCOMPBENEF/>
                  <PARENTESCOBENEF/>
                  <PORCENTAJEBENEF>0</PORCENTAJEBENEF>
                  <NOMBENEF/>
                  <PRIAPEBENEF/>
                  <SEGAPEBENEF/>
                  <TIPOIDBENEF/>
                  <TIPOIDBENEF>0</TIPOIDBENEF>
                  <IDBENEF>0</IDBENEF>
                  <IDBENEF/>
                  <NOMCOMPBENEF/>
                  <PARENTESCOBENEF/>
                  <PORCENTAJEBENEF>0</PORCENTAJEBENEF>
                  <NOMBENEF/>
                  <PRIAPEBENEF/>
                  <SEGAPEBENEF/>
                  <TIPOIDBENEF/>
                  <TIPOIDBENEF>0</TIPOIDBENEF>
                  <IDBENEF>0</IDBENEF>
                  <IDBENEF/>
                  <NOMCOMPBENEF/>
                  <PARENTESCOBENEF/>
                  <PORCENTAJEBENEF>0</PORCENTAJEBENEF>
                  <NOMBENEF/>
                  <PRIAPEBENEF/>
                  <SEGAPEBENEF/>
                </PARAMETROS>

                ]]>
              </Parametros>
            </Emision>
          </InfoSolicitud>
        </cot:EmisionClienteRequest>
      </soapenv:Body>
    </soapenv:Envelope>';
    // Generate the SOAP request
    $http_client = new \GuzzleHttp\Client();
    $params = ['body' => $myXMLData, 'headers' => ['Content-Type' => 'application/text',]];
    $request = $http_client->request('POST', $config->get('default_url_consume'), $params);
    // Clean the response
    $response = str_replace("<soapenv:Body>", "", $request->getBody()->getContents());
    $response = str_replace("</soapenv:Body>", "", $response);
    $response = str_replace('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">', "", $response);
    $response = str_replace('</soapenv:Envelope>', "", $response);
    $response = html_entity_decode($response);
    // LOGS TO CHECK IF CONECTION HAVE BEEN SUCCESS IN DEMO
    \Drupal::logger('ins_consume')->info($myXMLData);
    \Drupal::logger('ins_consume')->info(print_r($response, 1));
  }
}
