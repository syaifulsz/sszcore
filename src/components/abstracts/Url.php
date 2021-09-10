<?php

namespace sszcore\components\abstracts;

use sszcore\traits\ComponentTrait;
use sszcore\traits\ConfigPropertyTrait;
use sszcore\components\Str;
use sszcore\components\Session;

/**
 * Class Url
 * @package sszcore\components\abstracts
 * @since 0.1.5
 */
abstract class Url implements UrlInterface
{
    /**
     * @return string
     */
    public static function get_key_session_previous_url()
    {
        return 'SESSION_PREVIOUS_URL';
    }

    use ComponentTrait;
    use ConfigPropertyTrait;

    // components
    public $auth;
    public $helper;

    /**
     * Initialize Component Auth
     *
     * @TODO Need to configurize this into Configs
     * @return mixed
     */
    public function initializeComponentAuth( array $configs = [] )
    {
        $class = "\\sszcore\\component\\Auth";
        return $class::getInstance( $configs );
    }

    /**
     * Initialize Component UrlHelper
     *
     * @TODO Need to configurize this into Configs
     * @param Url $componentUrl
     * @return mixed
     */
    public function initializeUrlHelper( $componentUrl )
    {
        $class = "\\sszcore\\components\\ViewUrlHelper\\ViewUrlHelper";
        return new $class( $componentUrl );
    }

    /**
     * @param array $configs
     */
    public function __construct( array $configs = [] )
    {
        if ( !method_exists( $this, 'getInstance' ) ) {
            throw new \Error( 'Missing Singleton! This class should be use with Singleton!' );
        }

        // components
        $this->auth = $this->initializeComponentAuth();

        /**
         * Setup View Url Helper
         * To register Url Helper, go to `app/components/ViewUrlHelper` and create new components and add property for
         * that component in app/components/ViewUrlHelper/ViewUrlHelper.php
         */
        $this->helper = $this->initializeUrlHelper( $this );
    }

    /**
     * @param string $url
     * @return string
     */
    public function base( string $url = '' ) : string
    {
        $url = ltrim( $url, '/' );
        return $this->config->get( 'app.baseUrl' ) . ( $url ? "/{$url}" : '' );
    }

    /**
     * @param string $url
     * @param string $subdomain
     * @return string
     */
    public function subdomain( string $url = '', string $subdomain = '' ) : string
    {
        $base = $this->config->get( 'app.baseUrl' );
        $global = $this->config->get( 'app.globalBaseUrl' );
        $scheme = 'http';
        if ( Str::contains( $base, 'https' ) ) {
            $scheme = 'https';
        }
        $url = ltrim( $url, '/' );
        return "{$scheme}://{$subdomain}.{$global}" . ( $url ? "/{$url}" : '' );
    }

    /**
     * @param string $url
     * @return string
     */
    public function siteBase( string $url = '' ) : string
    {
        $url = ltrim( $url, '/' );
        return $this->config->get( 'app.siteBaseUrl' ) . ( $url ? "/{$url}" : '' );
    }

    /**
     * @param string $name
     * @param array $params
     * @return string
     */
    public function baseTo( string $name, array $params = [] ) : string
    {
        return $this->base( $this->to( $name, $params ) );
    }

    /**
     * @param string $name
     * @param array $params
     * @return string
     */
    public function to( string $name, array $params = [] ) : string
    {
        $query = [];
        if ( $url = $this->config->getRouteRules( $name ) ) {

            $url = ltrim( $url[ 0 ], '/' );
            $request = $_GET;

            // find all variables in uri rules
            preg_match( '/\{[a-zA-Z0-9]+\}/', $url, $uriVars );

            // checks if route rule require parameter
            if ( $uriVars ) {

                if ( !$params && !$request ) {
                    error_log( "Route rule name \"{$name}\" require parameter!" );
                    // throw new Exception( "Route rule name \"{$name}\" require parameter!" );
                }

                foreach ( $uriVars as $uriVar ) {
                    $uriVar = str_replace( '{', '', $uriVar );
                    $uriVar = str_replace( '}', '', $uriVar );

                    if ( isset( $request[ $uriVar ] ) && !isset( $params[ $uriVar ] ) ) {
                        $params[ $uriVar ] = $request[ $uriVar ];
                    } else if ( !isset( $params[ $uriVar ] ) ) {
                        error_log( "Route rule name \"{$name}\" is missing {$uriVar} parameter!" );
                        // throw new Exception( "Route rule name \"{$name}\" is missing {$uriVar} parameter!" );
                    }
                }
            }

            if ( $params ) {

                foreach ( $params as $key => $value ) {
                    $__key = '{' . $key . '}';
                    if ( Str::contains( $url, $__key ) ) {
                        $url = str_replace( $__key, $value, $url );
                    } else {
                        $query[ $key ] = $value;
                    }
                }
            }
            $url = rtrim( preg_replace( '/\{\w+\}/', '', $url ), '/' ) . ( $query ? '?' . http_build_query( $query ) : '' );
        }

        return $url ? "/{$url}" : '/';
    }

    /**
     * @param $url
     * @param bool $permanent
     */
    public function redirect( $url, bool $permanent = false )
    {
        header('Location: ' . $url, true, $permanent ? 301 : 302 );
        exit();
    }

    /**
     * @param string $url
     * @param string $alert_message
     * @param string $alert_type
     * @param array $alert_data
     */
    public function redirectWithAlertMessage( string $url, string $alert_message = '', string $alert_type = 'info', array $alert_data = [] )
    {
        Session::getInstance()->alert( $alert_message, $alert_type, $alert_data );
        $this->redirect( $url );
    }

    /**
     * @param string $key
     * @return array
     */
    public function currentArray( string $key = '' )
    {
        if ( empty( $_SERVER[ 'REQUEST_URI' ] ) ) {
            return null;
        }

        $uriCurrent = parse_url( $_SERVER[ 'REQUEST_URI' ] );
        $uriCurrentQuery = [];
        if ( !empty( $uriCurrent[ 'query' ] ) ) {
            parse_str( $uriCurrent[ 'query' ], $uriCurrentQuery );
        }
        $uriCurrent[ 'query' ] = $uriCurrentQuery;

        if ( $key ) {
            return data_get( $uriCurrent, $key );
        }

        return $uriCurrent;
    }

    public function capturePrevious()
    {
        $url = $_SERVER[ 'HTTP_REFERER' ] ?? '';
        if ( $url && Str::contains( $url, $this->base() ) ) {
            Session::getInstance()->set( self::get_key_session_previous_url(), $url );
        }
    }

    /**
     * @return string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function previous()
    {
        if ( !$prev = Session::getInstance()->get( self::get_key_session_previous_url() ) ) {
            $prev = $_SERVER[ 'HTTP_REFERER' ] ?? '';
        }

        return $prev && Str::contains( $prev, $this->base() ) ? $prev : '';
    }

    /**
     * @param array $params
     * @return string
     */
    public function current( array $params = [] ) : string
    {
        $uriCurrent = parse_url( $_SERVER[ 'REQUEST_URI' ] );
        $query = $params ? '?' . http_build_query( $params ) : '';

        if ( !empty( $uriCurrent[ 'query' ] ) ) {

            // remove clear cache
            if ( Str::contains( $uriCurrent[ 'query' ], 'clearCache=refresh' ) ) {
                $uriCurrent[ 'query' ] = str_replace( 'clearCache=refresh', '', $uriCurrent[ 'query' ] );
            }

            parse_str( $uriCurrent[ 'query' ], $queryArray );
            $params = array_merge( $queryArray, $params );

            foreach ( $params as $key => $value ) {
                if ( !$value ) {
                    unset( $params[ $key ] );
                }
            }

            $query = $params ? '?' . http_build_query( $params ) : '';
        }

        return $uriCurrent[ 'path' ] . $query;
    }
}
