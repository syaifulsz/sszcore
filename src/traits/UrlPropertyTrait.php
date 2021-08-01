<?php

namespace sszcore\traits;

/**
 * Trait UrlPropertyTrait
 * @package sszcore\traits
 * @since 0.1.5
 */
trait UrlPropertyTrait
{
    use ComponentTrait;

    /**
     * @TODO Need to configurize this into Configs
     * @return null
     */
    public function initializeComponentUrl()
    {
        return null;
    }

    /**
     * @return mixed
     */
    public function getUrlAttribute()
    {
        if ( !isset( $this->attributes[ 'url' ] ) ) {
            if ( !$init = $this->initializeComponentUrl() ) {
                throw new \Error( 'Missing Component Initialization for Url!' );
            }
            return $this->attributes[ 'url' ] = $init;
        }
        return $this->attributes[ 'url' ] ?? null;
    }
}