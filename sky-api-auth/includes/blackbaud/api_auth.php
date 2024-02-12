<?php

namespace blackbaud;

class Api_auth
{
    private static $settingsName = 'blackbaud_donations_api_auth';
    private static $api_auth = null;


    public static function getSettings()
    {
        if (is_array(self::$api_auth)) {
            return self::$api_auth;
        }

        return self::$api_auth = get_option(self::$settingsName, null);
    }

    public static function setToken($token = array())
    {
        self::$api_auth = null;
        update_option(self::$settingsName, $token);
    }


    public static function getAccessToken()
    {
        $api_auth = self::getSettings();

        return isset($api_auth['access_token']) ? $api_auth['access_token'] : null;
    }


    public static function getRefreshToken()
    {
        $api_auth = self::getSettings();

        return isset($api_auth['refresh_token']) ? $api_auth['refresh_token'] : null;
    }


    public static function isAuthenticated()
    {
        $api_auth = self::getSettings();

        return isset($api_auth['access_token']);
    }


    public static function logout()
    {
        delete_option(self::$settingsName);
    }
}
