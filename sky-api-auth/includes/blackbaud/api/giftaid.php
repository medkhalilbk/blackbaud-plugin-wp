<?php

namespace blackbaud;

class Giftaid
{
    private static $headers = array();
    private static $baseUri;

    public static function init()
    {
        self::$headers = array(
            'bb-api-subscription-key: ' . RENXT_SUBSCRIPTION_KEY,
            'Authorization: Bearer ' . Api_auth::getAccessToken()
        );
        self::$baseUri = SKY_API_BASE_URI . 'nxt-data-integration/v1/';
    }

    public static function getTaxDeclarationsForConstituent($constituent_id)
    {
        $url = self::$baseUri . "re/giftaid/constituents/$constituent_id/taxdeclarations";
        $headers = self::$headers;
        $headers[] = 'Content-type: application/json';

        $response = Http::get($url, $headers, true);

        return json_decode($response, true);
    }

    public static function createTaxDeclaration($body)
    {
        $url = self::$baseUri . 're/giftaid/taxdeclarations';
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

Giftaid::init();
