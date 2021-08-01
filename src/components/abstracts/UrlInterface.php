<?php

namespace sszcore\components\abstracts;

/**
 * Interface Url
 * @package sszcore\components\abstracts
 * @since 0.1.5
 */
interface UrlInterface
{
    /**
     * @return mixed
     */
    public static function getInstance( $configs );

    /**
     * @return string
     */
    public static function get_key_session_previous_url();

    /**
     * @param array $configs
     * @return mixed
     */
    public function initializeComponentAuth( array $configs = [] );

    /**
     * Initialize Component UrlHelper
     *
     * @TODO Need to configurize this into Configs
     * @param Url $componentUrl
     * @return mixed
     */
    public function initializeUrlHelper( $componentUrl );

    /**
     * @param array $configs
     */
    public function __construct( array $configs = [] );

    /**
     * @param string $url
     * @return string
     */
    public function base( string $url = '' );

    /**
     * @param string $url
     * @param string $subdomain
     * @return string
     */
    public function subdomain( string $url = '', string $subdomain = '' );

    /**
     * @param string $url
     * @return string
     */
    public function siteBase( string $url = '' );


    /**
     * @param string $name
     * @param array $params
     * @return string
     */
    public function baseTo( string $name, array $params = [] );

    /**
     * @param string $name
     * @param array $params
     * @return string
     */
    public function to( string $name, array $params = [] );

    /**
     * @param $url
     * @param bool $permanent
     */
    public function redirect( $url, bool $permanent = false );

    /**
     * @param string $key
     * @return array
     */
    public function currentArray( string $key = '' );

    public function capturePrevious();

    /**
     * @return string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function previous();

    /**
     * @param array $params
     * @return string
     */
    public function current( array $params = [] );
}
