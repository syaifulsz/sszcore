<?php

namespace sszcore\components;

use sszcore\traits\ComponentTrait;

/**
 * Class Profiler
 * @package sszcore\components
 * @since 0.1.0
 */
class Profiler
{
    protected static $instance = [];
    public static function getInstance( array $configs = [] ) : self
    {
        $key = md5( http_build_query( $configs ) );
        if ( !isset( self::$instance[ $key ] ) ) {
            self::$instance[ $key ] = new self( $configs );
        }
        return self::$instance[ $key ];
    }

    use ComponentTrait;

    public $data = [];

    /**
     * @param string $str
     */
    public function create( string $str )
    {
        $this->data[ $str ] = microtime(true);
    }

    /**
     * @param bool $render
     * @return array|string
     */
    public function output( bool $render = false )
    {
        $data = [];
        $prevTimed = microtime( true );
        foreach( $this->data as $profile => $timed ) {
            $duration = abs( $prevTimed - $timed );
            $hours = (int)( $duration / 60 / 60 );
            $minutes = (int)( $duration / 60 ) - $hours * 60;
            $seconds = (int)$duration - $hours * 60 * 60 - $minutes * 60;
            $prevTimed = $timed;
            $data[ $profile ] = "{$hours}h {$minutes}m {$seconds}s";
        }

        if ( $render ) {
            foreach( $data as $profile => $timed ) {
                return "<div><strong>{$profile}:</strong> {$timed}</div>";
            }
        } else {
            return $data;
        }
    }
}