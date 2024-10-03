<?php
namespace Drupal\osc_settings\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Controlador para devolver el contenido de las páginas definidas
 */
/**
 * Controlador para devolver el contenido de las páginas definidas
 */
class OrderList extends ControllerBase {
    public function showOrder($date) {
        $full_date = date_parse_from_format('Y-m', $date);
        //- Get number of days of month
        $number_of_days_of_month = cal_days_in_month(CAL_GREGORIAN, $full_date['month'], $full_date['year']);
        //- Get the timestamp of the first minute in the month
        $first_minute_of_month = mktime(0, 0, 0, $full_date['month'], 1, $full_date['year']);
        //- Get the timestamp of the last minute in the month
        $last_minute_of_month = mktime(23, 59, 59, $full_date['month'], $number_of_days_of_month, $full_date['year']);

        $query = \Drupal::entityQuery('commerce_order')
            ->condition('type', 'Default')
            ->condition('state', 'validation')
            ->condition('placed', [$first_minute_of_month, $last_minute_of_month], 'BETWEEN');
        $result = $query->execute();
        if ($result) {
            $orders = \Drupal::entityTypeManager()->getStorage('commerce_order')->loadMultiple($result);
            $table = $this->createTable($orders);
            $build['#table'] = $table;
            $form_class = '\Drupal\osc_settings\Form\ExportOrderForm';
            $build['#form'] = \Drupal::formBuilder()->getForm($form_class, $table);

        }
        $build['#title'] = "Reporte del mes de " . \Drupal::service('date.formatter')->format(
                strtotime($date), 'custom', 'F'
        );
        $build['#theme'] = 'osc_settings_orders_report';
        $build['#date']['month']['number'] = $full_date['month'];
        $build['#date']['month']['name'] = \Drupal::service('date.formatter')->format(
            strtotime($date), 'custom', 'F'
        );
        $build['#date']['year'] = $full_date['year'];

        $starting_year = date('Y', strtotime('-10 year'));
        $earliest_year = 2019;
        $latest_year = date('Y');
        foreach ( range( $latest_year, $earliest_year ) as $i ) {
            $build['#years'][] = $i;
        }
        return $build;
    }

    /**
     * This function create the table structure and show the data in the user view.
     * @param $order All order data.
     */
    public function createTable($orders) {
        $tableData = [];
        foreach ($orders as $key => $order) {
            $tableData[$key]['mail'] = $order->getEmail();
            $tableData[$key]['price']['number'] = round($order->getTotalPrice()->getNumber());
            $tableData[$key]['price']['currency'] = $order->getTotalPrice()->getCurrencyCode();
            $tableData[$key]['user'] = $order->getCustomerId();
            $tableData[$key]['field_card_order'] = json_decode($order->field_card_order->getValue()[0]['value']);
            $tableData[$key]['field_payment_data'] = json_decode($order->field_payment_data->getValue()[0]['value']);
            $tableData[$key]['field_contract_data_form'] = json_decode($order->field_contract_data_form->getValue()[0]['value']);
            if (isset($order->field_beneficiaries_data) && isset($order->field_beneficiaries_data->getValue()[0])) {

                $beneficiaries = $order->field_beneficiaries_data->getValue()[0]['value'];
                if ($beneficiaries) {
                    $tableData[$key]['field_beneficiaries_data'] = json_decode($beneficiaries);
                }
            }

            $date = $order->placed->getValue()[0]['value'];
            $tableData[$key]['date'] = \Drupal::service('date.formatter')->format(
                $date, 'custom', 'd-m-Y h:iA'
            );

        }
        return $tableData;
    }
}