<?php

namespace sszcore\traits;

use sszcore\components\Request;
use sszcore\components\Str;

/**
 * Trait ComponentTrait
 * @package sszcore\traits
 * @since 0.1.3
 *
 * @property string site_id
 * @property string site_env
 * @property string site_dir
 * @property string app_dir
 * @property boolean is_cli
 * @property boolean is_ajax
 * @property Request request
 */
trait ComponentTrait
{
    protected $original = [];
    protected $attributes = [];

    /**
     * @param array $attributes
     */
    public function setAttributes( array $attributes )
    {
        if ( $this->original = $attributes ) {
            foreach ( $attributes as $property => $value ) {
                $this->$property = $value;
            }
        }
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get( $name )
    {
        $method = 'get' . Str::studly( $name ) . 'Attribute';
        $property = "_{$name}";

        if ( method_exists( $this, $method ) ) {
            return $this->$method();
        }

        if ( property_exists( $this, $property ) ) {
            return $this->$property;
        }

        if ( isset( $this->attributes[ $name ] ) ) {
            return $this->attributes[ $name ];
        }

        return null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set( $name, $value )
    {
        $this->attributes[ $name ] = $value;

        $method = 'set' . Str::studly( $name ) . 'Attribute';
        $property = "_{$name}";

        switch ( true ) {

            case method_exists( $this, $method ) :
                $this->$method( $value );
                break;

            case property_exists( $this, $property ) :
                $this->$property = $value;
                break;

            default:
                $this->attributes[ $name ] = $value;
                break;
        }
    }

    /**
     * @return string
     */
    public function getSiteIdAttribute()
    {
        if ( !isset( $this->attributes[ 'site_id' ] ) ) {
            return $this->attributes[ 'site_id' ] = getenv( 'SITE_ID' );
        }

        return $this->attributes[ 'site_id' ] ?? '';
    }

    /**
     * @return string
     */
    public function getSiteDirAttribute()
    {
        if ( !isset( $this->attributes[ 'site_dir' ] ) ) {
            return $this->attributes[ 'site_dir' ] = getenv( 'SITE_DIR' );
        }
        return $this->attributes[ 'site_dir' ] ?? '';
    }

    /**
     * @return string
     */
    public function getAppDirAttribute()
    {
        if ( !isset( $this->attributes[ 'app_dir' ] ) && $this->site_dir ) {
            return $this->attributes[ 'app_dir' ] = str_replace( "/sites/{$this->site_id}", '', $this->site_dir ) . '/app';
        }
        return $this->attributes[ 'app_dir' ] ?? '';
    }

    /**
     * @return string
     */
    public function getSiteEnvAttribute()
    {
        if ( !isset( $this->attributes[ 'site_env' ] ) ) {
            $env = getenv( 'SITE_ENV' );
            return $this->attributes[ 'site_env' ] = strtolower( $env );
        }
        return $this->attributes[ 'site_env' ] ?? '';
    }

    /**
     * @return boolean
     */
    public function getIsCliAttribute()
    {
        if ( !isset( $this->attributes[ 'is_cli' ] ) ) {
            return $this->attributes[ 'is_cli' ] = ( php_sapi_name() === 'cli' );
        }

        return $this->attributes[ 'is_cli' ] ?? false;
    }

    /**
     * @return boolean
     */
    public function getIsAjaxAttribute()
    {
        if ( !$this->is_cli && !isset( $this->attributes[ 'is_ajax' ] ) ) {
            return $this->attributes[ 'is_ajax' ] = ( !empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) === 'xmlhttprequest' );
        }

        return $this->attributes[ 'isAjax' ] ?? false;
    }

    /**
     * @return Request|null
     */
    public function getRequestAttribute()
    {
        if ( !$this->is_cli && !isset( $this->attributes[ 'request' ] ) ) {
            return $this->attributes[ 'request' ] = Request::createFromGlobals();
        }
        return $this->attributes[ 'request' ] ?? null;
    }
}
