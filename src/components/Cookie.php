<?php

namespace sszcore\components;

/**
 * Class Cookie
 * @package sszcore\components
 * @since 0.1.5
 */
class Cookie
{
    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public static function get( string $key, $default = null )
    {
        $cookieValue = $_COOKIE[ $key ] ?? null;

        if ( !$cookieValue && $default ) {
            return $default;
        }

        if ( $decodedValue = json_decode( $cookieValue, true ) ) {
            return $decodedValue;
        }

        return $cookieValue;
    }

    /**
     * @param string $key
     * @param $value
     * @param int $duration
     * @param string $path
     */
    public static function set( string $key, $value, int $duration = 0, string $path = '/' )
    {
        setcookie( $key, $value, ( $duration ? time() + $duration : 0 ), $path, null, Request::isHttps(), false );
    }

    /**
     * @param string $key
     */
    public static function remove( string $key )
    {
        unset( $_COOKIE[ $key ] );
        setcookie( $key, null, -1, '/', null, false, false );
    }
}
