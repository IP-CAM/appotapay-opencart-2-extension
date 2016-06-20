<?php

class ModelPaymentAppotaPaymentGateway extends Model {

    private $allow_curency = array('VND');
    
    public function getMethod($address) {
        $this->load->language('payment/appota_payment_gateway');
        if ($this->config->get('appota_payment_gateway_status')) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('appota_payment_gateway_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

            if (!$this->config->get('appota_payment_gateway_geo_zone_id')) {
                $status = TRUE;
            } elseif ($query->num_rows) {
                $status = TRUE;
            } else {
                $status = FALSE;
            }
        } else {
            $status = FALSE;
        }
        
        $config_currency = $this->config->get('config_currency');
        if(!in_array($config_currency, $this->allow_curency)){
            $status = FALSE;
        }
        

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'appota_payment_gateway',
                'title' => $this->language->get('text_title'),
                'terms' => '',
                'sort_order' => $this->config->get('appota_payment_gateway_sort_order')
            );
        }

        return $method_data;
    }

}
