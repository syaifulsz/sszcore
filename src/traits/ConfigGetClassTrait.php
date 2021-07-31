<?php

namespace sszcore\traits;

/**
 * ConfigGetClassTrait
 * @package sszcore\traitsComponentTrait.php
 * @since 0.1.4
 */
trait ConfigGetClassTrait
{
    public function getClass( string $str, array $data = [] )
    {
        if ( $this->config ) {
            if (  is_a( $this->config, '\\app\\components\\Config' ) ||  is_a( $this->config, '\\sszcore\\components\\Config' ) ) {
                $class = $this->config->classMap( $str );
                return new $class( $data );
            }
        }
        return null;
    }
}