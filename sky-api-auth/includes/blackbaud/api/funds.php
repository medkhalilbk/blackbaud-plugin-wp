<?php

namespace blackbaud;

class Funds
{
    private static $headers = array();
    private static $baseUri;

    public static function init()
    {
        self::$headers = array(
            'bb-api-subscription-key: ' . RENXT_SUBSCRIPTION_KEY,
            'Authorization: Bearer ' . Api_auth::getAccessToken()
        );
        self::$baseUri = SKY_API_BASE_URI . 'fundraising/v1/';
    }

    public static function getAll()
    {
        $url = self::$baseUri . 'funds';
        $headers = self::$headers;
        $headers[] = 'Content-type: application/x-www-form-urlencoded';

        $response = Http::get($url, $headers);

        return json_decode($response, true);
    }

    public static function getById($id = 0)
    {
        $url = self::$baseUri . 'funds/' . $id;
        $headers = self::$headers;
        $headers[] = 'Content-type: application/x-www-form-urlencoded';
        $response = Http::get($url, $headers);
        return json_decode($response, true);
    }

    public static function update($fund = array())
    {
        $url = self::$baseUri . 'funds/' . $fund['fund_id'];
        $headers = self::$headers;
        $headers[] = 'Content-type: application/json';
        $response = Http::patch($url, $fund, $headers, true);
        return json_decode($response, true);
    }

    public static function lastResponseCode()
    {
        return Http::get_last_response_code();
    }
}

Funds::init();
