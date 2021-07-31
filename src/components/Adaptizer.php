<?php

namespace sszcore\components;

use sszcore\traits\ComponentTrait;

/**
 * Class Adaptizer
 * @package sszcore\components
 * @since 0.1.0
 */
class Adaptizer
{
    use ComponentTrait;

    /**
     * @var array
     */
    public static $instance = [];

    /**
     * @var \Mobile_Detect
     */
    public $mobileDetect;

    // properties
    public $isDesktop = true;
    public $isMobile = false;
    public $isTablet = false;
    public $isAndroid = false;

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
        // components
        $this->mobileDetect = new \Mobile_Detect;

        switch ( true ) {
            case $this->mobileDetect->isMobile() :
                $this->setMode( 'mobile' );
                break;
            case $this->mobileDetect->isTablet() :
                $this->setMode( 'tablet' );
                break;
            default :
                $this->setMode( 'desktop' );
                break;
        }

        $this->isAndroid = $this->mobileDetect->isAndroidOS();
    }

    /**
     * @param string $mode
     */
    public function setMode( string $mode )
    {
        switch ( $mode ) {
            case 'desktop' :
                $this->isDesktop = true;
                $this->isMobile = false;
                $this->isTablet = false;
                break;
            case 'mobile' :
                $this->isDesktop = false;
                $this->isMobile = true;
                $this->isTablet = false;
                break;
            case 'tablet' :
                $this->isDesktop = false;
                $this->isMobile = false;
                $this->isTablet = true;
                break;
        }
    }

    /**
     * @return string
     */
    public function getMode()
    {
        switch ( true ) {
            case $this->isMobile :
                return 'mobile';
            case $this->isTablet :
                return 'tablet';
            default :
                return 'desktop';
        }
    }

    /**
     * @return bool
     */
    public static function isDesktop()
    {
        return self::getInstance()->isDesktop;
    }

    /**
     * @return bool
     */
    public static function isMobile()
    {
        return self::getInstance()->isMobile;
    }

    /**
     * @return bool
     */
    public static function isTablet()
    {
        return self::getInstance()->isTablet;
    }

    /**
     * @return bool
     */
    public static function isAndroid()
    {
        return self::getInstance()->isTablet;
    }
}
