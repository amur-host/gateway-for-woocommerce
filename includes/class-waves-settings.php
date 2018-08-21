<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings class
 */
if (!class_exists('AmurSettings')) {

    class AmurSettings
    {

        public static function fields()
        {

            return apply_filters('wc_amur_settings',

                array(
                    'enabled'     => array(
                        'title'   => __('Enable/Disable', 'amur-gateway-for-woocommerce'),
                        'type'    => 'checkbox',
                        'label'   => __('Enable Amur payments', 'amur-gateway-for-woocommerce'),
                        'default' => 'yes',
                    ),
                    'title'       => array(
                        'title'       => __('Title', 'amur-gateway-for-woocommerce'),
                        'type'        => 'text',
                        'description' => __('This controls the title which the user sees during checkout.', 'amur-gateway-for-woocommerce'),
                        'default'     => __('Pay with Amur', 'amur-gateway-for-woocommerce'),
                        'desc_tip'    => true,
                    ),
                    'description' => array(
                        'title'   => __('Customer Message', 'amur-gateway-for-woocommerce'),
                        'type'    => 'textarea',
                        'default' => __('Ultra-fast and secure checkout with Amur'),
                    ),
                    'address'     => array(
                        'title'       => __('Destination address', 'amur-gateway-for-woocommerce'),
                        'type'        => 'text',
                        'default'     => '',
                        'description' => __('This addresses will be used for receiving funds.', 'amur-gateway-for-woocommerce'),
                    ),
                    'show_prices' => array(
                        'title'   => __('Convert prices', 'amur-gateway-for-woocommerce'),
                        'type'    => 'checkbox',
                        'label'   => __('Add prices in Amur (or asset)', 'amur-gateway-for-woocommerce'),
                        'default' => 'no',

                    ),
                    'secret'      => array(
                        'type'    => 'hidden',
                        'default' => sha1(get_bloginfo() . Date('U')),

                    ),
                    'asset_id'     => array(
                        'title'       => __('Asset ID', 'amur-gateway-for-woocommerce'),
                        'type'        => 'text',
                        'default'     => null,
                        'description' => __('This is the asset Id used for transactions.', 'amur-gateway-for-woocommerce'),
                    ),
                    'asset_code'     => array(
                        'title'       => __('Asset code (short name = currency code = currency symbol)', 'amur-gateway-for-woocommerce'),
                        'type'        => 'text',
                        'default'     => null,
                        'description' => __('This is the Asset Currency code for exchange rates. If omitted Amur will be used', 'amur-gateway-for-woocommerce'),
                    ),
                    'asset_description'     => array(
                        'title'       => __('Asset description', 'amur-gateway-for-woocommerce'),
                        'type'        => 'text',
                        'default'     => null,
                        'description' => __('Asset full name', 'amur-gateway-for-woocommerce'),
                    ),
                )
            );
        }
    }

}
