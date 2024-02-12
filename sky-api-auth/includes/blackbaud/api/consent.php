<?php

namespace blackbaud;

class Consent
{
    private static $headers = array();
    private static $baseUri;

    public static function init()
    {
        self::$headers = array(
            'bb-api-subscription-key: ' . RENXT_SUBSCRIPTION_KEY,
            'Authorization: Bearer ' . Api_auth::getAccessToken()
        );
        self::$baseUri = SKY_API_BASE_URI . 'commpref/v1/';
    }

    public static function create($body)
    {
        $url = self::$baseUri . 'consent/consents';
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

Consent::init();
