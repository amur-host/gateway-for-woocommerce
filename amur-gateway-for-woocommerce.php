<?php

/**
 * Amur Gateway for Woocommerce
 *
 * Plugin Name: AMUR Gateway for Woocommerce (also for other Amur assets)
 * Plugin URI: https://github.com/amur-host/gateway-for-woocommerce
 * Description: Show prices in Amur (or asset) and accept Amur payments in your woocommerce webshop
 * Version: 0.4.4
 * Author: amur.host
 * Author URI:   https://github.com/amur-host/gateway-for-woocommerce
 * License: GPLv2 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: amur-gateway-for-woocommerce
 * Domain Path: /languages/
  *
 * Copyright 2018 amur.host
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WcAmur')) {

    class WcAmur
    {

        private static $instance;
        public static $version = '0.4.1';
        public static $plugin_basename;
        public static $plugin_path;
        public static $plugin_url;

        protected function __construct()
        {
        	self::$plugin_basename = plugin_basename(__FILE__);
        	self::$plugin_path = trailingslashit(dirname(__FILE__));
        	self::$plugin_url = plugin_dir_url(self::$plugin_basename);
            add_action('plugins_loaded', array($this, 'init'));
        }
        
        public static function getInstance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function init()
        {
            $this->initGateway();
        }

        public function initGateway()
        {

            if (!class_exists('WC_Payment_Gateway')) {
                return;
            }

            if (class_exists('WC_Amur_Gateway')) {
	            return;
	        }

	        /*
	         * Include gateway classes
	         * */
	        include_once plugin_basename('includes/base58/src/Base58.php');
	        include_once plugin_basename('includes/base58/src/ServiceInterface.php');
	        include_once plugin_basename('includes/base58/src/GMPService.php');
	        include_once plugin_basename('includes/base58/src/BCMathService.php');
	        include_once plugin_basename('includes/class-amur-gateway.php');
	        include_once plugin_basename('includes/class-amur-api.php');
	        include_once plugin_basename('includes/class-amur-exchange.php');
	        include_once plugin_basename('includes/class-amur-settings.php');
	        include_once plugin_basename('includes/class-amur-ajax.php');

	        add_filter('woocommerce_payment_gateways', array($this, 'addToGateways'));
            add_filter('woocommerce_currencies', array($this, 'AmurCurrencies'));
            add_filter('woocommerce_currency_symbol', array($this, 'AmurCurrencySymbols'), 10, 2);

	        add_filter('woocommerce_get_price_html', array($this, 'AmurFilterPriceHtml'), 10, 2);
	        add_filter('woocommerce_cart_item_price', array($this, 'AmurFilterCartItemPrice'), 10, 3);
	        add_filter('woocommerce_cart_item_subtotal', array($this, 'AmurFilterCartItemSubtotal'), 10, 3);
	        add_filter('woocommerce_cart_subtotal', array($this, 'AmurFilterCartSubtotal'), 10, 3);
	        add_filter('woocommerce_cart_totals_order_total_html', array($this, 'AmurFilterCartTotal'), 10, 1);

	    }

	    public static function addToGateways($gateways)
	    {
	        $gateways['amur'] = 'WcAmurGateway';
	        return $gateways;
	    }

        public function AmufCurrencies( $currencies )
        {
            $currencies['AMUR'] = __( 'Amur', 'amur' );
            $currencies['WNET'] = __( 'Wavesnode.NET', 'wnet' );
            $currencies['ARTcoin'] = __( 'ARTcoin', 'ARTcoin' );
            $currencies['POL'] = __( 'POLTOKEN.PL', 'POL' );
            $currencies['Wykop Coin'] = __( 'WYKOP.PL', 'Wykop Coin' );
			$currencies['Surfcash'] = __( 'Surfcash', 'surfcash' );
			$currencies['TN'] = __( 'TurtleNode', 'tn' );
			$currencies['Ecop'] = __( 'Ecop', 'Ecop' );
            return $currencies;
        }

        public function AmurCurrencySymbols( $currency_symbol, $currency ) {
            switch( $currency ) {
                case 'AMUR': $currency_symbol = 'AMUR'; break;
                case 'WNET': $currency_symbol = 'WNET'; break;
                case 'ARTcoin': $currency_symbol = 'ARTcoin'; break;
                case 'POL': $currency_symbol = 'POL'; break;
                case 'Wykop Coin': $currency_symbol = 'Wykop Coin'; break;
				case 'Surfcash': $currency_symbol = 'surfcash'; break;
				case 'TN': $currency_symbol = 'TN'; break;
				case 'Ecop': $currency_symbol = 'Ecop'; break;
            }
            return $currency_symbol;
        }

	    public function AmurFilterCartTotal($value)
	    {
	        return $this->convertToAmurPrice($value, WC()->cart->total);
	    }

	    public function AmurFilterCartItemSubtotal($cart_subtotal, $compound, $that)
	    {
	        return $this->convertToAmurPrice($cart_subtotal, $that->subtotal);
	    }

	    public function AmurFilterPriceHtml($price, $that)
	    {
	        return $this->convertToAmurPrice($price, $that->price);
	    }

	    public function AmurFilterCartItemPrice($price, $cart_item, $cart_item_key)
	    {
	        $item_price = ($cart_item['line_subtotal'] + $cart_item['line_subtotal_tax']) / $cart_item['quantity'];
	        return $this->convertToAmurPrice($price,$item_price);
	    }

	    public function AmurFilterCartSubtotal($price, $cart_item, $cart_item_key)
	    {
	        $subtotal = $cart_item['line_subtotal'] + $cart_item['line_subtotal_tax'];
	        return $this->convertToAmurPrice($price, $subtotal);
	    }

	    private function convertToAmurPrice($price_string, $price)
	    {
            $options = get_option('woocommerce_amur_settings');
            if(!in_array(get_woocommerce_currency(), array("AMUR","WNET","ARTcoin","POL","Wykop Coin","Surfcash","TN","Ecop")) && $options['show_prices'] == 'yes') {
                $amur_currency = $options['asset_code'];
                if(empty($amur_currency)) {
                    $amur_currency = 'Amur';
                }
                $amur_assetId = $options['asset_id'];
                if(empty($amur_assetId)) {
                    $amur_assetId = null;
                }
                $amur_price = AmurExchange::convertToAsset(get_woocommerce_currency(), $price,$amur_assetId);
                if ($amur_price) {
                    $price_string .= '&nbsp;(<span class="woocommerce-price-amount amount">' . $amur_price . '&nbsp;</span><span class="woocommerce-price-currencySymbol">'.$amur_currency.')</span>';
                }
            }
	        return $price_string;
	    }
    }

}

WcAmur::getInstance();

function amurGateway_textdomain() {
    load_plugin_textdomain( 'amur-gateway-for-woocommerce', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
        
add_action( 'plugins_loaded', 'amurGateway_textdomain' );