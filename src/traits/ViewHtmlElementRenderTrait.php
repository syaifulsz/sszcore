<?php

namespace sszcore\traits;

/**
 * Trait ViewHtmlElementRenderTrait
 * @package sszcore\traits
 */
trait ViewHtmlElementRenderTrait
{
    /**
     * @param array $classNames
     * @return string
     */
    public function renderClassNames( array $classNames ) : string
    {
        return implode( ' ', array_unique( $classNames ) );
    }

    /**
     * @param array $style
     * @return string
     */
    public function renderStyle( array $style = [] ) : string
    {
        $__style = [];
        foreach ( $style as $key => $val ) {
            $__style[] = "{$key}: {$val};";
        }
        return implode( ' ', $__style );
    }

    /**
     * @param array $attributes
     * @return string
     */
    public function renderAttributes( array $attributes = [] ) : string
    {
        $render = [];
        foreach ( $attributes as $key => $value ) {
            $render[] = "{$key}=\"{$value}\"";
        }
        return implode( ' ', array_unique( $render ) );
    }
}
