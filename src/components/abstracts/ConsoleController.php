<?php

namespace sszcore\components\abstracts;

use sszcore\traits\ConfigPropertyTrait;
use sszcore\traits\ConsoleComponentTrait;
use sszcore\traits\UrlPropertyTrait;

/**
 * Class ConsoleController
 * @package sszcore\components\abstracts
 *
 * @property boolean console_debug
 */
abstract class ConsoleController
{
    use ConsoleComponentTrait;
    use ConfigPropertyTrait;
    use UrlPropertyTrait;

    /**
     * @param array $configs
     */
    public function __construct( array $configs = [] )
    {
        $componentConfig = $this->initializeComponentConfig( $configs );
        $reflection = new \ReflectionClass( $this );
        $this->echo( 'Console Controller: ' . $reflection->getName() );
    }

    public function getConsoleDebugAttribute()
    {
        if ( !isset( $this->attributes[ 'console_debug' ] ) ) {
            return $this->attributes[ 'console_debug' ] = getenv( 'CONSOLE_DEBUG' ) === '1';
        }

        return $this->attributes[ 'console_debug' ] ?? false;
    }

    public function run()
    {
        // do something
    }
}
