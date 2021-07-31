<?php

namespace sszcore\components;

use sszcore\traits\CachePropertyTrait;
use sszcore\traits\ComponentTrait;
use sszcore\traits\ConfigPropertyTrait;
use sszcore\traits\SessionPropertyTrait;
use sszcore\traits\SingletonTrait;

/**
 * Class Auth
 * @package sszcore\components
 * @since 0.1.5
 *
 * @TODO To configure User and Guest models via Config
 */
class Auth
{
    use ComponentTrait;
    use SingletonTrait;

    use CachePropertyTrait;
    use ConfigPropertyTrait;
    use SessionPropertyTrait;

    const AUTH_JWT_KEY                  = 'AUTH_JWT_KEY_SSZCORE1';
    const AUTH_PASSWORD_SALT            = 'AUTH_PASSWORD_SALT_SSZCORE1';
    const AUTH_COOKIE_GUEST_KEY         = 'AUTH_COOKIE_GUEST_KEY_SSZCORE1';
    const AUTH_COOKIE_KEY               = 'AUTH_COOKIE_KEY_SSZCORE1';
    const AUTH_INTERNAL_API_KEY         = 'Auth-Internal-Api-Key';

    public function __construct( array $configs = [] )
    {

    }

    /**
     * @return string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function get_auth_jwt_key()
    {
        return Config::getInstance()->get( 'auth.auth_jwt_key', self::AUTH_JWT_KEY );
    }

    /**
     * @return string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function get_auth_password_salt()
    {
        return Config::getInstance()->get( 'auth.auth_password_salt', self::AUTH_PASSWORD_SALT );
    }

    /**
     * @return string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function get_auth_cookie_guest_key()
    {
        return Config::getInstance()->get( 'auth.auth_cookie_guest_key', self::AUTH_COOKIE_GUEST_KEY );
    }

    /**
     * @return string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function get_auth_cookie_key()
    {
        return Config::getInstance()->get( 'auth.auth_cookie_guest_key', self::AUTH_COOKIE_KEY );
    }

    /**
     * @return string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function get_auth_internal_api_key()
    {
        return Config::getInstance()->get( 'auth.auth_cookie_guest_key', self::AUTH_INTERNAL_API_KEY );
    }
}
