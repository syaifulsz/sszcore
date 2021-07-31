<?php

namespace sszcore\traits;

/**
 * Trait SingletonTrait
 * @package sszcore\traits
 * @since 0.1.2
 */
trait SingletonTrait
{
    protected static $instance = [];
    public static function getInstance( array $configs = [] ) : self
    {
        $key = md5( http_build_query( $configs ) );
        if ( !isset( self::$instance[ $key ] ) ) {
            self::$instance[ $key ] = new self( $configs );
        }
        return self::$instance[ $key ];
    }
}