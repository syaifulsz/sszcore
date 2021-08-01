<?php

namespace sszcore\traits;

use sszcore\components\Adaptizer;

/**
 * Trait AdaptizerPropertyTrait
 * @package sszcore\traits
 * @since 0.1.5
 *
 * @property Adaptizer adaptizer
 */
trait AdaptizerPropertyTrait
{
    use ComponentTrait;

    /**
     * @return Adaptizer
     */
    public function getAdaptizerAttribute()
    {
        if ( !isset( $this->attributes[ 'adaptizer' ] ) ) {
            return $this->attributes[ 'adaptizer' ] = Adaptizer::getInstance();
        }
        return $this->attributes[ 'adaptizer' ] ?? null;
    }
}