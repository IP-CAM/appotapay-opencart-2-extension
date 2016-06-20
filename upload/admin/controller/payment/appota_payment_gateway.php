<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of appota_payment
 *
 * @author Longnh
 */
class ControllerPaymentAppotaPaymentGateway extends Controller {
    private $allow_curency = array('VND');
    //put your code here
    private $error = array();

    public function index() {
        // Load file language for module appota payment
        $this->load->language('payment/appota_payment_gateway');
        // Set title for module. $this->language->get('heading_title'): get heading_title from language
        $this->document->setTitle($this->language->get('heading_title'));
        // Load model setting
        $this->load->model('setting/setting');
        
        // Check if post to server and data is validated
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            // Save setting  of appota payment gateway to database
            $this->model_setting_setting->editSetting('appota_payment_gateway', $this->request->post);

            // Get success message for user
            $this->session->data['success'] = $this->language->get('text_success');
		
            // Redirect after save appota payment settings successfully
            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], true));
        }
        // Get text for buttons and messages
        $data['heading_title'] = $this->language->get('heading_title');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_search'] = $this->language->get('button_search');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['entry_apikey'] = $this->language->get('entry_apikey');
        $data['entry_apisecret'] = $this->language->get('entry_apisecret');
        $data['entry_apiprivate'] = $this->language->get('entry_apiprivate');
        $data['text_enabled'] = $this->language->get('text_enabled');
	$data['text_disabled'] = $this->language->get('text_disabled');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_order_status'] = $this->language->get('entry_order_status');

        // Get url for link and form tag
        $data['action'] = $this->url->link('payment/appota_payment_gateway', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], true);


        // Get error warning message
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['error_apikey'])) {
            $data['error_apikey'] = $this->error['error_apikey'];
        } else {
            $data['error_apikey'] = '';
        }

        if (isset($this->error['error_apisecret'])) {
            $data['error_apisecret'] = $this->error['error_apisecret'];
        } else {
            $data['error_apisecret'] = '';
        }

        if (isset($this->error['error_apiprivate'])) {
            $data['error_apiprivate'] = $this->error['error_apiprivate'];
        } else {
            $data['error_apiprivate'] = '';
        }

        // Get breadcrumbs
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/appota_payment', 'token=' . $this->session->data['token'], true)
        );

        // Get value for appota_apikey input
        if (isset($this->request->post['appota_payment_gateway_apikey'])) {
            $data['appota_payment_gateway_apikey'] = $this->request->post['appota_payment_gateway_apikey'];
        } else {
            $data['appota_payment_gateway_apikey'] = $this->config->get('appota_payment_gateway_apikey');
        }

        // Get value for appota_apisecret input
        if (isset($this->request->post['appota_payment_gateway_apisecret'])) {
            $data['appota_payment_gateway_apisecret'] = $this->request->post['appota_payment_gateway_apisecret'];
        } else {
            $data['appota_payment_gateway_apisecret'] = $this->config->get('appota_payment_gateway_apisecret');
        }

        // Get value for appota_apiprivate input
        if (isset($this->request->post['appota_payment_gateway_apiprivate'])) {
            $data['appota_payment_gateway_apiprivate'] = $this->request->post['appota_payment_gateway_apiprivate'];
        } else {
            $data['appota_payment_gateway_apiprivate'] = $this->config->get('appota_payment_gateway_apiprivate');
        }
        
        // Get value for appota_apiprivate input
        if (isset($this->request->post['appota_payment_gateway_status'])) {
            $data['appota_payment_gateway_status'] = $this->request->post['appota_payment_gateway_status'];
        } else {
            $data['appota_payment_gateway_status'] = $this->config->get('appota_payment_gateway_status');
        }
        
        if (isset($this->request->post['appota_payment_gateway_order_status_id'])) {
            $data['appota_payment_gateway_order_status_id'] = $this->request->post['appota_payment_gateway_order_status_id'];
        } else {
            $data['appota_payment_gateway_order_status_id'] = $this->config->get('appota_payment_gateway_order_status_id');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();



        // Load header, left side bar, footer of admin
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        // Render data to view file
        $this->response->setOutput($this->load->view('payment/appota_payment_gateway', $data));
    }

    protected function validate() {
        
        $config_currency = $this->config->get('config_currency');
        if(!in_array($config_currency, $this->allow_curency)){
            $this->error['warning'] = $this->language->get('text_currency_error');
        }
        
        if (!$this->user->hasPermission('modify', 'payment/appota_payment')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['appota_payment_gateway_apikey']) {
            $this->error['error_apikey'] = $this->language->get('error_apikey');
        }

        if (!$this->request->post['appota_payment_gateway_apisecret']) {
            $this->error['error_apisecret'] = $this->language->get('error_apisecret');
        }

//        if (!$this->request->post['appota_apiprivate']) {
//            $this->error['error_apiprivate'] = $this->language->get('error_apiprivate');
//        }


        return !$this->error;
    }

}
