<?php

namespace sszcore\traits;

use sszcore\components\Config;

/**
 * Trait ConfigPropertyTrait
 * @package sszcore\traits
 * @since 0.1.5
 *
 * @property Config config
 */
trait ConfigPropertyTrait
{
    use ComponentTrait;

    /**
     * @return Config
     */
    public function getConfigAttribute()
    {
        if ( !isset( $this->attributes[ 'config' ] ) ) {
            return $this->attributes[ 'config' ] = Config::getInstance();
        }
        return $this->attributes[ 'config' ] ?? null;
    }

    /**
     * Initialize Component Config
     *
     * @return Config
     */
    public function initializeComponentConfig( array $configs = [] )
    {
        return $this->config;
    }
}