<?php
namespace blackbaud;

$options = get_option( 'blackbaud_donations_settings' );

define('AUTH_CLIENT_ID',        @$options['client_id']);
define('AUTH_CLIENT_SECRET',    @$options['client_secret']);
define('RENXT_SUBSCRIPTION_KEY', @$options['subscription_key']);
define('PAYMENTS_SUBSCRIPTION_KEY', @$options['payments_subscription_key']);
define('PAYMENTS_PUBLIC_KEY', @$options['payments_public_key']);
define('PAYMENTS_CONFIG_ID',     @$options['payments_config_id']);
define('AUTH_REDIRECT_URI',     @$options['auth_redirect_url']);
define('AUTH_BASE_URI',         'https://oauth2.sky.blackbaud.com/');
define('SKY_API_BASE_URI',      'https://api.sky.blackbaud.com/');