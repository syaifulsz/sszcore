<?php

namespace sszcore\components;

use sszcore\traits\ComponentTrait;
use sszcore\traits\SingletonTrait;

/**
 * Class Session
 * @package sszcore\components
 * @since 0.1.0
 * @since 0.1.5 - Use Cookie Component
 */
class Session
{
    use ComponentTrait;
    use SingletonTrait;

    /**
     * @param array $configs
     */
    public function __construct( array $configs = [] )
    {
        if ( !isset( $_SESSION ) ) {
            session_start();
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public function set( $key, $value ) {
        $_SESSION[ $key ] = serialize( $value );
    }

    /**
     * @param $key
     * @return mixed|string
     */
    public function get( $key ) {
        return isset( $_SESSION[ $key ] ) ? unserialize( $_SESSION[ $key ] ) : '';
    }

    /**
     * @param array     $message
     * @param string    $key
     */
    public function setMessage( array $message, string $key = '' )
    {
        /**
         * Covert message data to array if it is Closure or Illuminate/Collection
         */
        if ( isset( $message[ 'data' ] ) && is_a( $message[ 'data' ], 'Closure' ) ) {
            if ( method_exists( $message[ 'data' ], 'toArray' ) ) {
                $message[ 'data' ] = $message[ 'data' ]->toArray();
            } else {
                $message[ 'data' ] = [];
            }
        }

        if ( isset( $_SESSION[ 'messages' ] ) && is_array( $_SESSION[ 'messages' ] ) ) {

            if ( $key ) {
                $_SESSION[ 'messages' ][ $key ] = $message;
            } else {
                $_SESSION[ 'messages' ][] = $message;
            }

        } else {

            $_SESSION[ 'messages' ] = [];

            if ( $key ) {
                $_SESSION[ 'messages' ][ $key ] = $message;
            } else {
                $_SESSION[ 'messages' ] = [
                    $message
                ];
            }
        }
    }

    /**
     * @param   string      $key
     * @param   null        $default
     * @return  mixed|null
     */
    public function getMessage( string $key, $default = null )
    {
        $messages = $this->getMessages();
        return $messages[ $key ] ?? $default;
    }

    /**
     * @return array
     */
    public function getMessages() : array
    {
        return $_SESSION[ 'messages' ] ?? [];
    }

    /**
     * @return array
     */
    public function getDataValidator() : array
    {
        return $_SESSION[ 'messages' ][ 0 ][ 'data' ][ 'validator' ] ?? [];
    }

    public function clearMessages()
    {
        unset( $_SESSION[ 'messages' ] );
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getCookie( string $key )
    {
        return Cookie::get( $key );
    }

    /**
     * @param string $key
     * @param $value
     * @param int $duration
     * @param string $path
     */
    public static function setCookie( string $key, $value, int $duration = 0, string $path = '/' )
    {
        Cookie::set( $key, $value, $duration, $path );
    }

    /**
     * @param string $key
     */
    public static function removeCookie( string $key )
    {
        Cookie::remove( $key );
    }

    /**
     * @param string $message
     * @param string $type
     * @param array $data
     */
    public function alert( string $message, string $type = 'info', array $data = [] )
    {
        $this->setMessage( [
            'tag'       => 'alert',
            'type'      => $type,
            'message'   => $message,
            'data'      => $data
        ], 'alert' );
    }
}
