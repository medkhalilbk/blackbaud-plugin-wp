<?php
/*
Plugin Name: Blackbaud Donations
description: Accept donations from your users using the blackbaud platform
Version: 2.0
Requires at least: 6.0
Requires PHP: 5.2
Author: MDUK Media
Author URI: https://mdukmedia.com
License: GPL2
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}


define( 'BLACKBAUD_DONATIONS__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BLACKBAUD_DONATIONS__DB_VERSION', '2.0');

register_activation_hook( __FILE__, array( 'Blackbaud_donations', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'Blackbaud_donations', 'plugin_deactivation' ) );

require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/sky-api-auth/config-options.php';
require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/sky-api-auth/includes/blackbaud/api_auth.php';
require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/sky-api-auth/includes/blackbaud/auth.php';
require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/sky-api-auth/includes/blackbaud/http.php';
require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/sky-api-auth/includes/blackbaud/api/funds.php';
require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/sky-api-auth/includes/blackbaud/api/checkout.php';
require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/sky-api-auth/includes/blackbaud/api/constituents.php';
require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/sky-api-auth/includes/blackbaud/api/consent.php';
require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/sky-api-auth/includes/blackbaud/api/gifts.php';
require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/sky-api-auth/includes/blackbaud/api/giftaid.php';
require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/sky-api-auth/includes/blackbaud/api/giftnote.php';
require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/class.blackbaud-donations.php';

add_action( 'init', array( 'Blackbaud_donations', 'init' ) );

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
    require_once( BLACKBAUD_DONATIONS__PLUGIN_DIR . 'class.blackbaud-donations-admin.php' );
    add_action( 'init', array( 'Blackbaud_donations_admin', 'init' ) );
}


/**
 *
 * Blackbaud donations plugin functions
 *
 */

function get_donations_cart_total($decimals = 2, $dec_point = '.', $thousands_sep = ',') {
    return class_exists('Blackbaud_donations') ? Blackbaud_donations::get_donations_total($decimals, $dec_point, $thousands_sep) : 0.00;
}

function get_donations_giftaid_total($total) {
    $removecommas = str_replace(',', '', $total);
    return number_format($removecommas * 0.25, 2);
}

function get_donations_sidebar() {
    if ( ! class_exists('Blackbaud_donations') ) {
        return '<div id="donations-cart-html">The blackbaud donations plugin is not enabled!</div>';
    }

    echo '<div id="donations-cart-html">';
    require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/templates/quick_donate_sidebar.php';
    echo '</div>';
}

/**
 * Quick donation form
 */
function quick_donation_form($atts) {
    if ( ! class_exists('Blackbaud_donations') ) {
        return '';
    }

    $default = array(
        'id' => NULL,
        'amounts' => NULL,
    );
    $form = shortcode_atts($default, $atts);

    if ( ! isset($form['id']) OR trim($form['id']) == '' ) {
        return '<p style="color:red">The donation form cannot be displayed because an invalid fund ID was provided to the shortcode. Please provide the fund ID within the shortcode parameters e.g. [quick_donation_form id="1"]</p>';
    }

    if( ! isset($form['amounts']) ) {
        $form['amounts'] = "30, 60, 120, 150";
    }

    $amounts = [];
    if (strpos($form['amounts'], ',') !== false) {
        $amount_bits = array_map('trim', explode(',', $form['amounts']));

        foreach($amount_bits as $amount) {
            $amounts[] = number_format($amount, 2, '.', '');
        }
    } else {
        $amounts[] = number_format($form['amounts'], 2, '.', '');
    }

    $funds = [];
    if (strpos($form['id'], ',') !== false) {
        $id_bits = array_map('trim', explode(',', $form['id']));

        foreach($id_bits as $id) {
            $funds[] = Blackbaud_donations::get_fund($id);
        }
    } else {
        $funds[] = Blackbaud_donations::get_fund($form['id']);
    }

    require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/templates/quick_donate.php';
}
add_shortcode('quick_donation_form', 'quick_donation_form');


/**
 * Appeal specific donation form
 *
 * @param $atts
 * @return string
 */
function appeal_donation_form($atts) {
    if ( ! class_exists('Blackbaud_donations') ) {
        return '';
    }

    $default = array(
        'id' => NULL,
        'amounts' => "10, 30, 50",
        'selected_amount' => NULL,
        'min_amount' => 0,
        'hide_other_amount' => false,
    );
    $form = shortcode_atts($default, $atts);

    if ( ! isset($form['id']) OR trim($form['id']) == '' ) {
        return '<p style="color:red">The donation form cannot be displayed because an invalid fund ID was provided to the shortcode. Please provide the fund ID within the shortcode parameters e.g. [appeal_donation_form id="1"]</p>';
    }

    $funds = [];
    if (strpos($form['id'], ',') !== false) {
        $id_bits = array_map('trim', explode(',', $form['id']));

        foreach($id_bits as $id) {
            $funds[] = Blackbaud_donations::get_fund($id);
        }
    } else {
        $funds[] = Blackbaud_donations::get_fund($form['id']);
    }

    $amounts = [];
    if (strpos($form['amounts'], ',') !== false) {
        $amount_bits = array_map('trim', explode(',', $form['amounts']));

        foreach($amount_bits as $amount) {
            $amounts[] = number_format($amount, 2, '.', '');
        }
    } else {
        $amounts[] = number_format($form['amounts'], 2, '.', '');
    }

    $selected_amount = isset($amounts[0]) ? $amounts[0] : '';
    if(isset($form['selected_amount'])) {
        $selected_amount = number_format($form['selected_amount'], 2, '.', '');
    }

    // get list of unique donation types for all funds given in the shortcode
    $donation_types = [];
    foreach($funds as $fund) {
        if( ! isset($fund['type']) ) {
            continue;
        }
        $donation_types[$fund['type']] = $fund['type'];
    }

    require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/templates/appeal.php';
}
add_shortcode('appeal_donation_form', 'appeal_donation_form');


/**
 * Quick appeal specific donation form
 *
 * @param $atts
 * @return string
 */
function quick_appeal_form($atts) {
    if ( ! class_exists('Blackbaud_donations') ) {
        return '';
    }

    $options = get_option( 'blackbaud_donations_options' );
    $quick_appeal_funds = json_decode($options['quick_appeals_json']);

    require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/templates/quick_appeal.php';
}
add_shortcode('quick_appeal_form', 'quick_appeal_form');


/**
 * Donation checkout form
 */
function donation_checkout_form() {
    if ( ! class_exists('Blackbaud_donations') ) {
        return '';
    }

    require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/templates/checkout.php';
}
add_shortcode('donation_checkout_form', 'donation_checkout_form');


/**
 * Recurring Donation form
 */
function recurring_donation_form() {
    if ( ! class_exists('Blackbaud_donations') ) {
        return '';
    }

    require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/templates/recurring.php';
}
add_shortcode('recurring_donation_form', 'recurring_donation_form');
