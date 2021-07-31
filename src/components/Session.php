<?php

namespace sszcore\components;

/**
 * Class Session
 * @package sszcore\components
 * @since 0.1.0
 */
class Session
{
    const MINUTE_IN_SECONDS     = 60;
    const HOUR_IN_SECONDS       = 3600;
    const DAY_IN_SECONDS        = 86400;
    const WEEK_IN_SECONDS       = 604800;
    const MONTH_IN_SECONDS      = 2592000;
    const YEAR_IN_SECONDS       = 31536000;

    // project specific properties
    protected $siteId;
    protected $siteDir;

    protected static $instance = [];

    /**
     * @param array $config
     * @return static
     */
    public static function getInstance( array $config = [] ) : self
    {
        $key = md5( http_build_query( $config ) );
        if ( !isset( self::$instance[ $key ] ) ) {
            self::$instance[ $key ] = new self( $config );
        }
        return self::$instance[ $key ];
    }

    public function __construct( array $configs = [] )
    {
        if ( !isset( $_SESSION ) ) {
            session_start();
        }

        // project specific properties
        $this->siteId = $configs[ 'siteId' ] ?? getenv( 'SITE_ID' );
        $this->siteDir = $configs[ 'siteDir' ] ?? getenv( 'SITE_DIR' );
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
        return !empty( $_COOKIE[ $key ] ) ? $_COOKIE[ $key ] : null;
    }

    /**
     * @param string $key
     * @param $value
     * @param int $duration
     * @param string $path
     */
    public static function setCookie( string $key, $value, int $duration = 0, string $path = '/' )
    {
        setcookie( $key, $value, time() + ( $duration ?: ( 5 * self::MINUTE_IN_SECONDS ) ), $path, null, Request::isHttps(), false );
    }

    /**
     * @param string $key
     */
    public static function removeCookie( string $key )
    {
        unset( $_COOKIE[ $key ] );
        setcookie( $key, null, -1, '/', false, false );
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
