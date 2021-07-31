<?php

namespace sszcore\traits;

use sszcore\components\Cache;

/**
 * Trait CachePropertyTrait
 * @package sszcore\traits
 * @since 0.1.5
 *
 * @property Cache cache
 */
trait CachePropertyTrait
{
    use ComponentTrait;

    /**
     * @return Cache
     */
    public function getCacheAttribute()
    {
        if ( !isset( $this->attributes[ 'cache' ] ) ) {
            return $this->attributes[ 'cache' ] = Cache::getInstance();
        }
        return $this->attributes[ 'cache' ] ?? null;
    }
}