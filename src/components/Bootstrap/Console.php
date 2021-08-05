<?php

namespace sszcore\components\Bootstrap;

use sszcore\components\Str;
use sszcore\traits\ConfigPropertyTrait;
use sszcore\traits\ConsoleComponentTrait;

/**
 * Class Console
 * @package sszcore\components\Bootstrap
 * @since 0.1.8
 *
 * @property array routes
 * @property array routeCommand
 */
class Console
{
    use ConsoleComponentTrait;
    use ConfigPropertyTrait;

    public $root_dir;
    public $database;

    public function __construct( array $attributes = [] )
    {
        $this->beforeInit();
        $this->setAttributes( $attributes );

        if ( isset( $attributes[ 'rootDir' ] ) || isset( $attributes[ 'root_dir' ] ) ) {
            $this->root_dir = $attributes[ 'rootDir' ]  ?? $attributes[ 'root_dir' ] ?? null;
        }

        if ( !$this->root_dir ) {
            throw new \Error( 'Attribute rootDir is required!' );
        }

        putenv( "SITE_DIR={$this->root_dir}/sites/" . getenv( 'SITE_ID' ) );

        $this->init();

        $listen = $attributes[ 'listen' ] ?? true;
        if ( $listen ) {
            $this->listen();
        }
    }

    /**
     * Initialize Component Database
     *
     * @TODO Need to configurize this into Configs
     * @return mixed
     */
    public function initializeComponentDatabase( array $configs = [] )
    {
        return null;
    }

    /**
     * @return array
     */
    public function getRoutesAttribute()
    {
        if ( !isset( $this->attributes[ 'routes' ] ) ) {

            $summary = [];
            foreach( $this->config->get( 'routesConsole' ) as $routes ) {
                foreach ( $routes[ 'routes' ] as $command => $route ) {
                    $summary[ $command ] = $route;
                }
            }

            return $this->attributes[ 'routes' ] = [
                'command' => $summary,
                'routes' => $this->config->get( 'routesConsole' ),
            ];
        }

        return $this->attributes[ 'routes' ] ?? [];
    }

    /**
     * @return array|null
     */
    public function getRouteCommandAttribute()
    {
        if ( !isset( $this->attributes[ 'routeCommand' ] ) && $this->command && isset( $this->routes[ 'command' ][ $this->command ] ) ) {
            return $this->attributes[ 'routeCommand' ] = $this->routes[ 'command' ][ $this->command ];
        }
        return $this->attributes[ 'routeCommand' ] ?? null;
    }

    protected function beforeInit()
    {
        // before init
    }

    protected function init()
    {
        if ( !$this->site_id ) {
            throw new \Error( 'SITE_ID is not set...' );
        }

        if ( !$this->site_env ) {
            throw new \Error( 'SITE_ENV is not set...' );
        }

        ini_set( 'display_errors', 1 );
        ini_set( 'error_log', __DIR__ . '/app/runtime/logs/console-' . date( 'Y-m-d' ) . '.log' );
        date_default_timezone_set( $this->config->get( 'app.timezone' ) );

        $this->database = $this->initializeComponentDatabase();

        // initiate database
        if ( $this->config->get(  'database.useMysql' ) ) {
            $this->database->capsule();
        }
    }

    public function listen()
    {
        $this->echoBreak();

        if ( $this->routeCommand ) {
            $this->echo( $this->routeCommand );
            $controller = new $this->routeCommand[ 'controller' ];
            $method = $this->routeCommand[ 'method' ] ?? 'run';
            return $controller->$method();
        } else {

            if ( $this->command && $this->command !== 'help' ) {
                $found = 0;
                foreach( $this->routes[ 'routes' ] as $title => $routes ) {
                    if ( Str::contains( $title, $this->command ) ) {
                        $this->echoBox( $routes[ 'name' ] . ' - ' . $routes[ 'description' ], '*', '*' );
                        foreach ( $routes[ 'routes' ] as $command => $route ) {
                            if ( Str::contains( $command, $this->command ) ) {
                                $found++;
                                $this->echo( "{$command}", 'yellow' );
                                $this->echo( '    ' . $route[ 'description' ] );
                                $this->echoBreak();
                            }
                        }
                        $this->echoBreak();
                    }
                }

                if ( !$found ) {
                    $this->echo( "Oops! No command found for \"{$this->command}\"", 'red' );
                    $this->echoBreak();
                }
                die();
            }

            foreach( $this->routes[ 'routes' ] as $title => $routes ) {
                $this->echoBox( $routes[ 'name' ] . ' - ' . $routes[ 'description' ], '*', '*' );
                foreach ( $routes[ 'routes' ] as $command => $route ) {
                    $this->echo( "{$command}", 'yellow' );
                    $this->echo( '    ' . $route[ 'description' ] );
                    $this->echoBreak();
                }
                $this->echoBreak();
            }
        }
    }
}
