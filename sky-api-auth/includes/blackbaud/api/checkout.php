<?php

namespace blackbaud;

class Checkout
{
    private static $headers = array();
    private static $baseUri;

    public static function init()
    {
        self::$headers = array(
            'bb-api-subscription-key: ' . PAYMENTS_SUBSCRIPTION_KEY,
            'Authorization: Bearer ' . Api_auth::getAccessToken()
        );
        self::$baseUri = SKY_API_BASE_URI . 'payments/v1/';
    }

    public static function completeTransaction($body)
    {
        $url = self::$baseUri . 'checkout/transaction';
        $headers = self::$headers;
        $headers[] = 'Content-type: application/json';

        $response = Http::post($url, $body, $headers, true);

        return json_decode($response, true);
    }

    public static function lastResponseCode()
    {
        return Http::get_last_response_code();
    }
}

Checkout::init();
