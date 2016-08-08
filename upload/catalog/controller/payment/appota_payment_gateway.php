<?php

include('catalog/controller/payment/appota_payment_gateway_logger.php');
include('catalog/controller/payment/appota_payment_gateway_call_api.php');
include('catalog/controller/payment/appota_payment_gateway_receiver.php');

class ControllerPaymentAppotaPaymentGateway extends Controller {

    protected $logger;

    public function index() {        
        
        $this->load->language('payment/appota_payment_gateway');
        $data = array();
        $data['button_confirm'] = $this->language->get('button_confirm');

        $data['continue'] = $this->url->link('payment/appota_payment_gateway/checkout', '', true);

        return $this->load->view('payment/appota_payment_gateway.tpl', $data);
    }

    public function checkout() {
        $this->load->language('payment/appota_payment_gateway');

        if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
            $this->response->redirect($this->url->link('checkout/cart'));
        }

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $url_success = $this->url->link('payment/appota_payment_gateway/success');
        $url_cancel = $this->url->link('payment/appota_payment_gateway/cancel');
        $total_amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
//        $shipping_amount = $this->shipping->getCost($this->session->get('shipping_method'));

        $shipping = array();
        // Because __call can not keep var references so we put them into an array. 
        $total_data = array(
            'totals' => &$shipping,
            'total' => 0
        );
        $this->load->model('total/shipping');
        $this->{'model_total_shipping'}->getTotal($total_data);

        $shipping_fee = $this->currency->format($shipping[0]['value'], $order_info['currency_code'], $order_info['currency_value'], false);
        
        $payer_name = $order_info['payment_lastname'] . $order_info['payment_firstname'];

        $params['order_id'] = (int)$this->session->data['order_id'];
        $params['total_amount'] = strval($total_amount);
        $params['shipping_fee'] = strval($shipping_fee);
        $params['tax_fee'] = strval("0");
        $params['currency_code'] = $order_info['currency_code'];
        $params['url_success'] = $url_success;
        $params['url_cancel'] = $url_cancel;
        $params['order_description'] = $order_info['comment'];
        $params['payer_name'] = $payer_name;
        $params['payer_email'] = $order_info['email'];
        $params['payer_phone_no'] = $order_info['telephone'];
        $params['payer_address'] = $order_info['shipping_address_1'] . " - " . $order_info['shipping_city'];
        $params['ip'] = $this->auto_reverse_proxy_pre_comment_user_ip();
        $params['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $products = $this->cart->getProducts();
        $items_data = array();
        foreach($products as $product) {
            $items_data[$product['product_id']]['id'] = $product['product_id'];
            $items_data[$product['product_id']]['name'] = $product['name'];
            $items_data[$product['product_id']]['quantity'] = $product['quantity'];
            $items_data[$product['product_id']]['price'] = $product['price'];
        }
        $params['product_info'] = json_encode($items_data);
        
        $config = array();
        $config['api_key'] = $this->config->get('appota_payment_gateway_apikey');
        $config['lang'] = $this->language->get('code');
        $config['secret_key'] = $this->config->get('appota_payment_gateway_apisecret');
        $config['ssl_verify'] = false;
        $config['private_key'] = $this->config->get('appota_payment_gateway_apiprivate');

        $call_api = new AppotaPaymentGatewayCallApi($config);
        $result = $call_api->getPaymentUrl($params);
        $registry = $this->registry;
        $this->logger = new AppotaPaymentGatewayLogger($registry);
        $noError = 1;
        if (empty($result)) {
            $message = $this->language->get('error_no_result_message');
            $noError = 0;
            $this->logger->writeLog($message);
        }
        if ($result['error'] != 0) {
            $message = $result['message'];
            $noError = 0;
            $this->logger->writeLog($result['message']);
        }
        if ($noError) {
            $appota_payment_url = $result['data']['payment_url'];
            $comment_status = "Success: Redirect Payment Url -> " . $appota_payment_url;
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('config_order_status_id'), $comment_status, false, false);
                
            $this->logger->writeLog($comment_status);
            $this->response->redirect($appota_payment_url);
        } else {
            $this->session->data['error'] = $message;
            $this->response->redirect($this->url->link('checkout/cart'));
        }
    }

    public function success() {
        $registry = $this->registry;
        $this->logger = new AppotaPaymentGatewayLogger($registry);
        
        $config['api_key'] = $this->config->get('appota_payment_gateway_apikey');
        $config['secret_key'] = $this->config->get('appota_payment_gateway_apisecret');
        
        $receiver = new AppotaPaymentGatewayReceiver($config, $registry);
        
        $check_valid_request = $receiver->checkValidRequest($_GET);
        $message = "";
        if ($check_valid_request['error_code'] == 0) {
            $check_valid_order = $receiver->checkValidOrder($_GET);
            if ($check_valid_order['error_code'] == 0) {
                $order_id = (int) $_GET['merchant_order_id'];
                $transaction_id = (int) $_GET['transaction_id'];
                $total_amount = floatval($_GET['total_amount']);
                $comment_status = 'Thực hiện thanh toán thành công với đơn hàng ' . $order_id . '. Giao dịch hoàn thành. Cập nhật trạng thái cho đơn hàng thành công';
                $this->load->model('checkout/order');
//                addOrderHistory($order_id, $order_status_id, $comment = '', $notify = false, $override = false)
                $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('appota_payment_gateway_order_status_id'), $comment_status, true, false);
                $message = "Appota Pay xác nhận đơn hàng: [Order ID: {$order_id}] - [Transaction ID: {$transaction_id}] - [Total: {$total_amount}] - [{$this->config->get('appota_payment_gateway_order_status_id')}]";
                $this->logger->writeLog($message);
                $this->response->redirect($this->url->link('checkout/success'));
            }else{
                $message = "Mã Lỗi: {$check_valid_order['error_code']} - Message: {$check_valid_order['message']}";
                $this->logger->writeLog($message);
                
                $this->session->data['error'] = $message;
                $this->response->redirect($this->url->link('checkout/cart'));
            }
        }else{
            $message = "Mã Lỗi: {$check_valid_request['error_code']} - Message: {$check_valid_request['message']}";
            $this->logger->writeLog($message);            
            $this->session->data['error'] = $message;
            $this->response->redirect($this->url->link('checkout/cart'));
        }
    }

    public function cancel() {
        $this->load->language('payment/appota_payment_gateway');
        $this->session->data['error'] = $this->language->get('error_cancel_payment_message');
        $this->response->redirect($this->url->link('checkout/cart'));
    }
    
    function auto_reverse_proxy_pre_comment_user_ip() {
        $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
        if (!empty($_SERVER['X_FORWARDED_FOR'])) {
            $X_FORWARDED_FOR = explode(',', $_SERVER['X_FORWARDED_FOR']);
            if (!empty($X_FORWARDED_FOR)) {
                $REMOTE_ADDR = trim($X_FORWARDED_FOR[0]);
            }
        }
        /*
         * Some php environments will use the $_SERVER['HTTP_X_FORWARDED_FOR'] 
         * variable to capture visitor address information.
         */ elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $HTTP_X_FORWARDED_FOR = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if (!empty($HTTP_X_FORWARDED_FOR)) {
                $REMOTE_ADDR = trim($HTTP_X_FORWARDED_FOR[0]);
            }
        }
        return preg_replace('/[^0-9a-f:\., ]/si', '', $REMOTE_ADDR);
    }

}
