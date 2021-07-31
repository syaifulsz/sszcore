<?php

namespace sszcore\components;

/**
 * Class AuthPassword
 * @package sszcore\components
 * @since 0.1.5
 */
class AuthPassword
{
    /**
     * @param string $password
     * @return string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function hash( string $password )
    {
        $salt = md5( Auth::get_auth_password_salt() );
        return md5( $salt . $password );
    }

    /**
     * @param string $password
     * @param string $toCompare
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function check( string $password, string $toCompare )
    {
        return self::hash( $password ) === $toCompare;
    }
}