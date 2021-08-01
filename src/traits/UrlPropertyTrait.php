<?php

namespace sszcore\traits;

use sszcore\components\Url;

/**
 * Trait UrlPropertyTrait
 * @package sszcore\traits
 * @since 0.1.5
 *
 * @property Url url
 */
trait UrlPropertyTrait
{
    use ComponentTrait;

    /**
     * @return Url
     */
    public function getUrlAttribute()
    {
        if ( !isset( $this->attributes[ 'url' ] ) ) {
            return $this->attributes[ 'url' ] = Url::getInstance();
        }
        return $this->attributes[ 'url' ] ?? null;
    }
}