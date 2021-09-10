<?php

namespace sszcore\traits;

use sszcore\components\Str;

/**
 * Trait CreateTemplateNameFromClassname
 * @package sszcore\traits
 * @since 0.2.2
 */
trait CreateTemplateNameFromClassname
{
    /**
     * @param string $namespace
     * @param string $method
     * @return string
     */
    public function createTemplateNameFromClassname( string $namespace, string $method = 'index' )
    {
        $recreateNamespaceArr = [];
        $namespaceData = explode( '\\', $namespace );

        $excludes = [
            'app',
            'controllers',
            'Controller'
        ];
        foreach ( $namespaceData as $t ) {
            if ( !in_array( $t, $excludes ) ) {
                $t = str_replace( 'Controller', '', $t );
                $recreateNamespaceArr[] = Str::kebab( $t );
            }
        }
        if ( $method ) {
            $recreateNamespaceArr[] = Str::kebab( $method );
        }

        return implode( '/', $recreateNamespaceArr );
    }
}