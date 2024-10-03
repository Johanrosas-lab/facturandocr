<?php

namespace Drupal\osc_settings\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Link;
class ExportOrderForm extends FormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'export_order_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $orders = NULL) {

        $form_state->setValue('order',$orders);
        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Exportar'),
            '#button_type' => 'primary',
        ];
        return $form;
    }


    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

        $orders = $form_state->getBuildInfo()['args'][0];
        $report = "";
        foreach ($orders as $order) {
            // Inicio
            $report .= "1;PREFIJO_SPONSOR;;;";
            foreach ($order['field_contract_data_form'] as $info_data) {
                // Tipo de cédula
                if (isset($info_data->field_tipo_de_documento_de_ident) && $info_data->field_tipo_de_documento_de_ident === 'Cédula nacional') {
                    $report .= "N;";
                }
                elseif (isset($info_data->field_tipo_de_documento_de_ident) && $info_data->field_tipo_de_documento_de_ident === 'Cédula residencia') {
                    $report .= "R;";
                }
                else {
                    $report .= "O;";
                }
                // Cédula;plan;total;aux;frecuencia de pago;tipo de cobertura;
                $report .= $info_data->field_numero_documento_de_identi . ';;' . $order['price']['number'] . ';AUX;;;';
                $date = \Drupal::service('date.formatter')->format(
                    strtotime($order['date']), 'custom', 'd/m/Y'
                );
                $name = array();
                if (\strpos($info_data->field_nomb, ' ') !== false) {
                    $name = explode(" ", $info_data->field_nomb);
                }
                else {
                    $name = [$info_data->field_nomb];
                }
                // date;vendedor;nombre del asegurado;segundo nombre;
                $report .= $date . ';;' . $name[0] . ';' . ((isset($name[1]) && $name[1]) ? $name[1] : '') . ';';


                $last_name = array();
                if (\strpos($info_data->field_apellidos_del_asegurado, ' ') !== false) {
                    $last_name = explode(" ", $info_data->field_apellidos_del_asegurado);
                }
                else {
                    $last_name = [$info_data->field_apellidos_del_asegurado];
                }
                // primer apellido;segundo apellido;
                $report .= $last_name[0] . ';' . ((isset($last_name[1]) && $last_name[1]) ? $last_name[1] : '') . ';';

                // direccion 1; direccion 2; direccion 3;distrito(3);canton(3);provincia(3);pais(3);
                $report .= ';;;;;;;';

                // telefono1;
                $report .= ((isset($info_data->field_telefono_celular_del_asegu) && $info_data->field_telefono_celular_del_asegu) ? $info_data->field_telefono_celular_del_asegu : '') . ';';

                // telefono2;
                $report .= ((isset($info_data->field_telefono_domicilio_del_ase) && $info_data->field_telefono_domicilio_del_ase) ? $info_data->field_telefono_domicilio_del_ase : '') . ';';

                $birthDate = \Drupal::service('date.formatter')->format(
                    strtotime($info_data->field_fecha_de_nacimiento_del_as), 'custom', 'd/m/Y'
                );
                // email;fecha de nacimiento;sexo;
                $report .= $info_data->field_email_del_asegurado . ';' . $birthDate . ';' . $info_data->field_sexo_del_asegurado . ';';

                // relacion dependiente;tarjeta;fecha de expiración;tipo de tarjeta;
                $report .= ';;' . $order['field_card_order']->expirationMonth . '/' . $order['field_card_order']->expirationYear . ';;';

                // numero de tarjeta;tipo de cuenta(3);codigo collector(5);Referencia 1;Referencia 2;Referencia 3;Referencia 4;Referencia 5;Referencia 6;tipo de beneficiario 1;
                $report .= $order['field_payment_data']->cardMasked . ';;;;;;;;;;';
                // tipo de beneficiario 1(1);Nombre benef; primer apellido;segundo apellido;tipo de indentificacion;cedula;relacion;porcentaje; (hasta el 5)
                $report .= ';;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;';
                $report .= "\n";

            }
        }
        $current_path = \Drupal::service('path.current')->getPath();
        $name = explode("/", $current_path);
        $directory = 'public://orders-report/';
        file_prepare_directory($directory, FILE_CREATE_DIRECTORY);
        $file = file_save_data($report,  $directory . $name[2] . '.txt', FILE_EXISTS_REPLACE);

        $url = Url::fromRoute('osc_settings.download_item', array('item_id' => $file->id()));
        $link5 = Link::fromTextAndUrl(t('Click aquí para descargar el archivo ') , $url);

        $list[] = $link5;
        $messenger = \Drupal::messenger();
        $messenger->addMessage($link5, $messenger::TYPE_STATUS);
        return;
    }

}