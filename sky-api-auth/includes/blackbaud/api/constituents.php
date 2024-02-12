<?php

namespace blackbaud;

class Constituents
{
    private static $headers = array();
    private static $baseUri;

    public static function init()
    {
        self::$headers = array(
            'bb-api-subscription-key: ' . RENXT_SUBSCRIPTION_KEY,
            'Authorization: Bearer ' . Api_auth::getAccessToken()
        );
        self::$baseUri = SKY_API_BASE_URI . 'constituent/v1/';
    }

    public static function getById($id = 0)
    {
        $url = self::$baseUri . 'constituents/' . $id;
        $headers = self::$headers;
        $headers[] = 'Content-type: application/x-www-form-urlencoded';
        $response = Http::get($url, $headers);
        return json_decode($response, true);
    }

    public static function search($email)
    {
        $url = self::$baseUri . "constituents/search?search_field=email_address&search_text=$email&limit=1&include_inactive=true";

        $headers = self::$headers;
        $headers[] = 'Content-type: application/x-www-form-urlencoded';

        $response = Http::get($url, $headers);

        return json_decode($response, true);
    }

    public static function create($constituent = array())
    {
        $url = self::$baseUri . 'constituents/';
        $headers = self::$headers;
        $headers[] = 'Content-type: application/json';

        $response = Http::post($url, $constituent, $headers, true);

        return json_decode($response, true);
    }

    public static function update($constituent_id, $constituent = array())
    {
        $url = self::$baseUri . 'constituents/' . $constituent_id;
        $headers = self::$headers;
        $headers[] = 'Content-type: application/json';
        $response = Http::patch($url, $constituent, $headers, true);
        return json_decode($response, true);
    }

    public static function lastResponseCode()
    {
        return Http::get_last_response_code();
    }
}

Constituents::init();
