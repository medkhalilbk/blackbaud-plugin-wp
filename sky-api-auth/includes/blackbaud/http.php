<?php

namespace blackbaud;

class Http
{
    private static $last_response_code = null;
    private static $last_response = null;

    /**
     * @param string $url
     * @param array $headers
     * @return mixed
     */
    public static function get($url = '', $headers = array())
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers
        ));

        return self::handle_response($ch);
    }

    /**
     * @param string $url
     * @param array $body
     * @param array $headers
     * @return mixed
     */
    public static function patch($url = '', $body = array(), $headers = array())
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, array(
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($body)
        ));

        return self::handle_response($ch);
    }

    /**
     * @param string $url
     * @param array $body
     * @param array $headers
     * @param bool $is_json
     * @return mixed
     */
    public static function post($url = '', $body = array(), $headers = array(), $is_json = false)
    {
        $ch = curl_init($url);

        $post = ($is_json) ? json_encode($body) : http_build_query($body);

        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $post
        ));

        return self::handle_response($ch);
    }

    /**
     * @param $code
     * @return mixed
     */
    public static function set_last_response_code($code)
    {
        return self::$last_response_code = $code;
    }

    /**
     * @return null
     */
    public static function get_last_response_code()
    {
        return self::$last_response_code;
    }

    /**
     * @param $response
     * @return mixed
     */
    public static function set_last_response($response)
    {
        return self::$last_response = $response;
    }

    /**
     * @return null
     */
    public static function get_last_response()
    {
        return self::$last_response;
    }

    /**
     * @param $ch
     * @return mixed
     */
    private static function handle_response($ch)
    {
        $response = self::set_last_response(curl_exec($ch));

        self::set_last_response_code(curl_getinfo($ch, CURLINFO_RESPONSE_CODE));

        curl_close($ch);

        return $response;
    }
}
