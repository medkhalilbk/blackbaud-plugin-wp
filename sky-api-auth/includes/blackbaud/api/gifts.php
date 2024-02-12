<?php

namespace blackbaud;

class Gifts
{
    private static $headers = array();
    private static $baseUri;

    public static function init()
    {
        self::$headers = array(
            'bb-api-subscription-key: ' . RENXT_SUBSCRIPTION_KEY,
            'Authorization: Bearer ' . Api_auth::getAccessToken()
        );
        self::$baseUri = SKY_API_BASE_URI . 'gift/v1/';
    }

    public static function getById($id = 0)
    {
        $url = self::$baseUri . 'gifts/' . $id;
        $headers = self::$headers;
        $headers[] = 'Content-type: application/x-www-form-urlencoded';
        $response = Http::get($url, $headers);
        return json_decode($response, true);
    }

    public static function create($constituent = array())
    {
        $url = self::$baseUri . 'gifts/';
        $headers = self::$headers;
        $headers[] = 'Content-type: application/json';
        $response = Http::post($url, $constituent, $headers, true);
        return json_decode($response, true);
    }

    public static function update($gift = array())
    {
        $url = self::$baseUri . 'gifts/' . $gift['gift_id'];
        $headers = self::$headers;
        $headers[] = 'Content-type: application/json';
        $response = Http::patch($url, $gift, $headers, true);
        return json_decode($response, true);
    }

    public static function lastResponseCode()
    {
        return Http::get_last_response_code();
    }
}

Gifts::init();
