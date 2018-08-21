<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajax class
 */
class AmurAjax
{

    private static $instance;

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        add_action('wp_ajax_check_amur_payment', array(__CLASS__, 'checkAmurPayment'));
    }

    public function checkAmurPayment()
    {
        global $woocommerce;
        $woocommerce->cart->get_cart();

        $options = get_option('woocommerce_amur_settings');

        $payment_total   = WC()->session->get('amur_payment_total');
        $destination_tag = WC()->session->get('amur_destination_tag');

        $ra     = new AmurApi($options['address']);
        $result = $ra->findByDestinationTag($destination_tag);

        $result['match'] = ($result['amount'] == $payment_total ) ? true : false;

        echo json_encode($result);
        exit();
    }

}

AmurAjax::getInstance();
