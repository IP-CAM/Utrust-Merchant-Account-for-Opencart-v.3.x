<?php

class ControllerExtensionPaymentUtrust extends Controller {

    var $API_ROOT;
    var $API_KEY;
    var $WEBHOOK_SECRET;


    public function index() {
        $this->load->language('extension/payment/utrust');
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['text_loading'] = $this->language->get('text_loading');
        $data['text_title'] = $this->language->get('text_title');

        return $this->load->view('extension/payment/utrust', $data);
    }

    public function payment() {

        $json = array();

        if ($this->session->data['payment_method']['code'] == 'utrust') {
            $this->load->language('extension/payment/utrust');
            $this->load->model('checkout/order');
            $this->load->model('extension/payment/utrust');

            $data = $this->model_checkout_order->getOrder($this->session->data['order_id']);


            $this->API_ROOT = $this->config->get('payment_utrust_api_url');
            $this->API_KEY = $this->config->get('payment_utrust_api_token');
            $this->WEBHOOK_SECRET = $this->config->get('payment_utrust_webhook_secret');

            $json['redirect'] = $this->url->link('checkout/failure');

            try {
                // get customer data
                if (isset($this->session->data['guest'])) {
                    $customer_email = $this->session->data['guest']['email'];
                    $customer_firstname = $this->session->data['guest']['firstname'];
                    $customer_lastname = $this->session->data['guest']['lastname'];
                } else {
                    $customer_email = $this->customer->getEmail();
                    $customer_firstname = $this->customer->getFirstName();
                    $customer_lastname = $this->customer->getLastName();
                }

                // get order items
                $line_items = [];

                $this->load->model('account/order');
                $products = $this->model_account_order->getOrderProducts($data['order_id']);

                $totals = $this->model_account_order->getOrderTotals($data['order_id']);


                foreach ($totals as $total) {
                    $data['totals'][$total['code']] = $total['value'];
                }
                foreach ($products as $product) {

                    $line_items[] = [
                        'sku' => $product['model'],
                        'name' => $product['name'],
                        'price' => number_format($product['price'], 2, ".", ""),
                        'currency' => $data['currency_code'],
                        'quantity' => $product['quantity'],
                    ];
                }

                $orderData = [
                    'reference' => $data['order_id'],
                    'amount' => [
                        'total' => number_format($data['total'], 2, ".", ""),
                        'currency' => $data['currency_code'],
                        'details' => [
                            'subtotal' => number_format($data['totals']['sub_total'], 2, ".", ""),
                        ],
                    ],
                    'return_urls' => [
                        'return_url' => $this->url->link('extension/payment/utrust/success'),
                        'cancel_url' => $this->url->link('extension/payment/utrust/cancel'),
                        'callback_url' => $this->url->link('extension/payment/utrust/callback'),
                    ],
                    'line_items' => $line_items,
                ];


                if (isset($data['totals']['shipping'])) {
                    $orderData['amount']['details']['shipping'] = number_format($data['totals']['shipping'], 2, ".", "");
                }

                if (isset($data['totals']['tax'])) {
                    $orderData['amount']['details']['tax'] = number_format($data['totals']['tax'], 2, ".", "");
                }

                if (isset($data['totals']['coupon'])) {
                    $orderData['amount']['details']['coupon'] = number_format($data['totals']['coupon'], 2, ".", "");
                }
                $this->log->write($orderData);
                $customerData = [
                    'first_name' => $customer_firstname,
                    'last_name' => $customer_lastname,
                    'email' => $customer_email,
                    'country' => $data['payment_country'],
                ];

                try {
                    $response = $this->createOrder($orderData, $customerData);
                    $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_utrust_order_status_id_pending'), $this->language->get('utrust_waiting'), true);
                    $this->cart->clear();
                    $json['redirect'] = $response->attributes->redirect_url;
                    // Use the $redirect_url to redirect the customer to our Payment Widget
                } catch (Exception $e) {
                    echo 'Something went wrong: ' . $e->getMessage();
                }

            } catch (Exception $e) {
                $this->model_extension_payment_utrust->logger(json_encode($e));
                $json['redirect'] = $this->url->link('checkout/decline');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }


    public function updateOrderState($payload) {
        $this->load->model('checkout/order');

        $event_type = $payload->event_type;
        $order_id = $payload->resource->reference;

        if ($event_type === "ORDER.PAYMENT.RECEIVED") {
            $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_utrust_order_status_id_paid'), '', true);
        } else if ($event_type === "ORDER.PAYMENT.CANCELLED") {
            $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_utrust_order_status_id_cancel'), '', true);
        }
    }


    public function callback() {


        //get payload
        $payload = file_get_contents('php://input');


        //get webhook secret from db
        $this->WEBHOOK_SECRET = $this->config->get('payment_utrust_webhook_secret');

        //transform payload into json object
        $payload = json_decode($payload);

        //get signature
        $signature = $payload->signature;

        if ($this->WEBHOOK_SECRET == null) {
            throw new Exception('Webhooks Secret cant be NULL!');
        }
        // Removes signature from response
        unset($payload->signature);

        $webhooksSecret = $this->WEBHOOK_SECRET;

        // Concat keys and values into one string
        $concatedPayload = [];
        foreach ($payload as $key => $value) {
            if (is_object($value)) {
                foreach ($value as $k => $v) {
                    $concatedPayload[] = $key;
                    $concatedPayload[] = $k . $v;
                }
            } else {
                $concatedPayload[] = $key . $value;
            }
        }
        // Sort array alphabetically
        ksort($concatedPayload);
        // Concat the array
        $concatedPayload = join('', $concatedPayload);
        // Sign string with HMAC SHA256
        $signedPayload = hash_hmac('sha256', $concatedPayload, $webhooksSecret);

        // Check if signature is correct
        if ($signature === $signedPayload) {
            $this->updateOrderState($payload);
            return true;
        }
        throw new Exception('Invalid signature!');
    }


    public function success() {
        if ($this->session->data['payment_method']['code'] == 'utrust') {
            $this->load->language('extension/payment/utrust'); // 
            $this->load->model('checkout/order');
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_utrust_order_status_id_pending'), $this->language->get('utrust_waiting'));
        }
        $this->response->redirect($this->url->link('checkout/success'));
    }

    public function cancel() {
        if ($this->session->data['payment_method']['code'] == 'utrust') {
            $this->load->language('extension/payment/utrust'); // 
            $this->load->model('checkout/order');
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_utrust_order_status_id_cancel'), $this->language->get('utrust_cancel'));
        }
        $this->response->redirect($this->url->link('checkout/checkout'));
    }


    public function createOrder($orderData, $customerData): ?object {
        // Build body
        $body = [
            'data' => [
                'type' => 'orders',
                'attributes' => [
                    'order' => $orderData,
                    'customer' => $customerData,
                ],
            ],
        ];

        $response = $this->post('stores/orders', $body);

        if (isset($response->errors)) {
            throw new Exception('Exception: Request Error! ' . print_r($response->errors, true));
        } elseif (!isset($response->data->attributes->redirect_url)) {
            throw new Exception('Exception: Missing redirect_url!');
        }
        return $response->data;
    }

    private function post($endpoint, array $body = []) {
        // Check the cURL handle has not already been initiated
        if ($this->curlHandle === null) {
            // Initiate cURL
            $this->curlHandle = curl_init();

            // Set options
            curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->curlHandle, CURLOPT_MAXREDIRS, 10);
            curl_setopt($this->curlHandle, CURLOPT_TIMEOUT, 30);
            curl_setopt($this->curlHandle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($this->curlHandle, CURLOPT_POST, 1);
        }

        // Set headers
        $headers = array();
        $headers[] = 'Authorization: Bearer ' . $this->API_KEY;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, $headers);

        // Set URL

        curl_setopt($this->curlHandle, CURLOPT_URL, $this->API_ROOT . 'stores/orders/');

        // Set body
        curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, json_encode($body));

        // Execute cURL
        $response = curl_exec($this->curlHandle);

        // Check the response of the cURL session
        if ($response !== false) {
            $result = false;

            // Prepare JSON result
            $decoded = json_decode($response);

            // Check the json decoding and set an error in the result if it failed
            if (!empty($decoded)) {
                $result = $decoded;
            } else {
                $result = ['error' => 'Unable to parse JSON result (' . json_last_error() . ')'];
            }
        } else {
            // Returns the error if the response of the cURL session is false
            $result = ['errors' => 'cURL error: ' . curl_error($this->curlHandle)];
        }

        return $result;
    }

}
