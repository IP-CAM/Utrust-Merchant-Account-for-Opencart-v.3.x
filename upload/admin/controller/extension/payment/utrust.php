<?php

class ControllerExtensionPaymentUtrust extends controller {

    private $error = array();

    public function index() {
        $this->load->language('extension/payment/utrust');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_utrust', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }


        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/utrust', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/utrust', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        $this->load->model('localisation/language');

        if (isset($this->request->post['payment_utrust_api_token'])) {
            $data['payment_utrust_api_token'] = $this->request->post['payment_utrust_api_token'];
        } else {
            $data['payment_utrust_api_token'] = $this->config->get('payment_utrust_api_token');
        }

        if (isset($this->request->post['payment_utrust_webhook_secret'])) {
            $data['payment_utrust_webhook_secret'] = $this->request->post['payment_utrust_webhook_secret'];
        } else {
            $data['payment_utrust_webhook_secret'] = $this->config->get('payment_utrust_webhook_secret');
        }

        if (isset($this->request->post['payment_utrust_api_url'])) {
            $data['payment_utrust_api_url'] = $this->request->post['payment_utrust_api_url'];
        } else {
            $data['payment_utrust_api_url'] = $this->config->get('payment_utrust_api_url');
        }


        if (isset($this->request->post['payment_utrust_order_status_id_paid'])) {
            $data['payment_utrust_order_status_id_paid'] = $this->request->post['payment_utrust_order_status_id_paid'];
        } else {
            $data['payment_utrust_order_status_id_paid'] = $this->config->get('payment_utrust_order_status_id_paid');
        }

        if (isset($this->request->post['payment_utrust_order_status_id_pending'])) {
            $data['payment_utrust_order_status_id_pending'] = $this->request->post['payment_utrust_order_status_id_pending'];
        } else {
            $data['payment_utrust_order_status_id_pending'] = $this->config->get('payment_utrust_order_status_id_pending');
        }

        if (isset($this->request->post['payment_utrust_order_status_id_failed'])) {
            $data['payment_utrust_order_status_id_failed'] = $this->request->post['payment_utrust_order_status_id_failed'];
        } else {
            $data['payment_utrust_order_status_id_failed'] = $this->config->get('payment_utrust_order_status_id_failed');
        }

        if (isset($this->request->post['payment_utrust_order_status_id_cancel'])) {
            $data['payment_utrust_order_status_id_cancel'] = $this->request->post['payment_utrust_order_status_id_cancel'];
        } else {
            $data['payment_utrust_order_status_id_cancel'] = $this->config->get('payment_utrust_order_status_id_cancel');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['payment_utrust_geo_zone_id'])) {
            $data['payment_utrust_geo_zone_id'] = $this->request->post['payment_utrust_geo_zone_id'];
        } else {
            $data['payment_utrust_geo_zone_id'] = $this->config->get('payment_utrust_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['payment_utrust_status'])) {
            $data['payment_utrust_status'] = $this->request->post['payment_utrust_status'];
        } else {
            $data['payment_utrust_status'] = $this->config->get('payment_utrust_status');
        }

        if (isset($this->request->post['payment_utrust_sort_order'])) {
            $data['payment_utrust_sort_order'] = $this->request->post['payment_utrust_sort_order'];
        } else {
            $data['payment_utrust_sort_order'] = $this->config->get('payment_utrust_sort_order');
        }

        // populate errors
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        if (isset($this->error['api_token'])) {
            $data['error_api_token'] = $this->error['api_token'];
        } else {
            $data['error_api_token'] = '';
        }


        if (isset($this->error['api_url'])) {
            $data['error_api_url'] = $this->error['api_url'];
        } else {
            $data['error_api_url'] = '';
        }

        if (isset($this->error['webhook_secret'])) {
            $data['error_webhook_secret'] = $this->error['webhook_secret'];
        } else {
            $data['error_webhook_secret'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/utrust', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/utrust')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if(!isset($this->request->post['payment_utrust_api_token'])  || trim($this->request->post['payment_utrust_api_token'] ) == ''){
            $this->error['api_token'] = $this->language->get('error_api_token');
        }

        if(!isset($this->request->post['payment_utrust_api_url'])  || trim($this->request->post['payment_utrust_api_url']) == ''){
            $this->error['api_url'] = $this->language->get('error_api_url');
        }
        if(!isset($this->request->post['payment_utrust_webhook_secret'])  || trim($this->request->post['payment_utrust_webhook_secret']) == ''){
            $this->error['webhook_secret'] = $this->language->get('error_webhook_secret');
        }



        return !$this->error;
    }

}
