<?php

namespace sszcore\traits;

use sszcore\components\Session;

/**
 * Trait SessionPropertyTrait
 * @package sszcore\traits
 * @since 0.1.5
 *
 * @property Session session
 */
trait SessionPropertyTrait
{
    use ComponentTrait;

    /**
     * @return Session
     */
    public function getSessionAttribute()
    {
        if ( !isset( $this->attributes[ 'session' ] ) ) {
            return $this->attributes[ 'session' ] = Session::getInstance();
        }
        return $this->attributes[ 'session' ] ?? null;
    }
}