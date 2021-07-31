<?php

namespace sszcore\components;

use sszcore\models\Component\Config\LocationState;
use sszcore\traits\ComponentTrait;
use sszcore\traits\SingletonTrait;

/**
 * Class Config
 * @package sszcore\components
 * @since 0.1.2
 *
 * @property string master_cache_key
 */
class Config
{
    use ComponentTrait;
    use SingletonTrait;

    // properties
    public $configs = [];

    /**
     * @var Adaptizer
     */
    protected $adaptizer;

    /**
     * @var Cache
     */
    protected $cache;

    // project specific properties
    public $site_subdomain;

    protected $cli = false;

    public $location_states = [];

    /**
     * @return string
     */
    public function getMasterCacheKeyAttribute()
    {
        if ( !isset( $this->attributes[ 'master_cache_key' ] ) ) {
            $this->attributes[ 'master_cache_key' ] = md5( http_build_query( [
                $this->site_id,
                $this->site_dir,
                $this->site_env,
                $this->adaptizer->getMode()
            ] ) );
        }
        return $this->attributes[ 'master_cache_key' ] ?? '';
    }

    public function __construct()
    {
        $this->cli = ( php_sapi_name() === 'cli' );

        $this->adaptizer = Adaptizer::getInstance();
        $this->cache = Cache::getInstance();

        // project specific properties
        $this->site_subdomain = ( getenv( 'SITE_SUBDOMAIN' ) !== 'www' ? getenv( 'SITE_SUBDOMAIN' ) : null );

        $this->configs = $this->buildConfig( [
            'site_id' => $this->site_id,
            'site_dir' => $this->site_dir,
            'site_env' => $this->site_env,
            'site_subdomain' => $this->site_subdomain,
        ] );
    }

    /**
     * @return string
     */
    public function getProjectName()
    {
        $envs = [
            'development',
            'staging',
            'production',
            'config',
            'local',
        ];
        $projectName = $this->site_env;
        foreach ( $envs as $env ) {
            $projectName = str_replace( "{$env}-", '', $projectName );
        }
        return $projectName;
    }

    /**
     * @param   string $env
     * @return  bool
     */
    public function isAppEnv( string $env )
    {
        return Str::contains( $this->site_env, $env );
    }

    /**
     * @param string $path
     * @return array
     */
    private function adaptiveConfig( string $path ) : array
    {
        $mode = !$this->adaptizer->isDesktop ? $this->adaptizer->getMode() : '';

        if ( !$this->adaptizer->isDesktop ) {
            $path = realpath( $path );
            $paths = explode( '/', $path );
            $lastKey = count( $paths ) - 1;
            $paths[ $lastKey ] = ( $mode ? $mode . '/' : '' ) . $paths[ $lastKey ];
            $file = implode( '/', $paths );
            if ( file_exists( $file ) ) {
                return require( $file );
            }
        }

        return [];
    }

    /**
     * @return string
     */
    public function getEnvName()
    {
        if ( Str::contains( $this->site_env, 'staging' ) ) {
            return 'staging';
        }

        if ( Str::contains( $this->site_env, 'production' ) ) {
            return 'production';
        }

        return 'development';
    }

    private function buildConfig( array $params = [], bool $fresh = false ) : array
    {
        $cachedConfigFile = "{$this->site_dir}/configs/cached/{$this->site_env}-cached.json";
        if ( file_exists( $cachedConfigFile ) && !Cache::isRefreshCache() && !$fresh ) {
            $config = file_get_contents( $cachedConfigFile );
            return json_decode( $config, true );
        }

        $config = [];
        $configDir = "{$this->app_dir}/configs/*.php";
        foreach ( glob( $configDir ) as $file ) {
            $configKey = pathinfo( $file, PATHINFO_FILENAME );
            $config[ $configKey ] = array_replace_recursive( require( $file ), $this->adaptiveConfig( $file ) );
        }
        
        if ( $this->site_env ) {
            $envConfig = [];
            $envConfigPath = "{$this->app_dir}/configs/{$this->site_env}.php";
            if ( file_exists( $envConfigPath ) ) {
                $config = array_replace_recursive( $config, require( $envConfigPath ), $this->adaptiveConfig( $envConfigPath ) );
            }
        }

        $local = [];
        $localPath = "{$this->app_dir}/configs/local.php";
        if ( file_exists( $localPath ) ) {
            $config = array_replace_recursive( $config, require( $localPath ), $this->adaptiveConfig( $localPath ) );
        }

        // project specific configs
        if ( $this->site_id ) {
            $projectDir = $this->site_dir . '/configs';
            $projectConfig = [];

            $projectConfigFiles = glob( $projectDir . '/*.php' );
            foreach ( $projectConfigFiles as $file ) {
                if (
                    !Str::contains( pathinfo( $file, PATHINFO_FILENAME ), 'config' ) &&
                    !Str::contains( pathinfo( $file, PATHINFO_FILENAME ), 'production' ) &&
                    !Str::contains( pathinfo( $file, PATHINFO_FILENAME ), 'staging' ) &&
                    !Str::contains( pathinfo( $file, PATHINFO_FILENAME ), 'development' ) &&
                    !Str::contains( pathinfo( $file, PATHINFO_FILENAME ), 'canary' ) &&
                    !Str::contains( pathinfo( $file, PATHINFO_FILENAME ), 'local' )

                    // Need to exclude routes because routes maybe contains additional logic that require Config calls
                    // !Str::contains( pathinfo( $file, PATHINFO_FILENAME ), 'routes' )
                ) {
                    $projectConfig[ pathinfo( $file, PATHINFO_FILENAME ) ] = array_replace_recursive( require( $file ), $this->adaptiveConfig( $file ) );
                }
            }

            if ( $this->site_env ) {

                /**
                 * Base Config
                 */
                $baseConfigPath = "{$projectDir}/config-". $this->getProjectName() .".php";
                if ( file_exists( $baseConfigPath ) ) {
                    $projectConfig = array_replace_recursive( $projectConfig, require( $baseConfigPath ), $this->adaptiveConfig( $baseConfigPath ) );
                }

                /**
                 * Environment Specific Config
                 */
                $envMainConfigPath = "{$projectDir}/". $this->getEnvName() .".php";
                if ( file_exists( $envMainConfigPath ) ) {
                    $projectConfig = array_replace_recursive( $projectConfig, require( $envMainConfigPath ), $this->adaptiveConfig( $envMainConfigPath ) );
                }

                /**
                 * Environment Project Specific Config
                 */
                $envConfigPath = "{$projectDir}/{$this->site_env}.php";
                if ( file_exists( $envConfigPath ) ) {
                    $projectConfig = array_replace_recursive( $projectConfig, require( $envConfigPath ), $this->adaptiveConfig( $envConfigPath ) );
                }

                /**
                 * Local Development Config
                 */
                if ( file_exists( "{$this->site_dir}/configs/{$this->site_env}-local.php" ) ) {
                    $appEnvConfigLocal = require( "{$this->site_dir}/configs/{$this->site_env}-local.php" );
                    $projectConfig = array_replace_recursive( $projectConfig, $appEnvConfigLocal );
                }
            }

            $local = [];
            $localConfigPath = $projectDir . '/local.php';
            if ( file_exists( $localConfigPath ) ) {
                $projectConfig = array_replace_recursive( $projectConfig, require( $localConfigPath ), $this->adaptiveConfig( $localConfigPath ) );
            }
            $config = array_replace_recursive( $config, $projectConfig );
        }

        $config = array_replace_recursive( $config, $params );

        file_put_contents( $cachedConfigFile, json_encode( $config ) );

        return $config;
    }

    /**
     * @param string $key
     * @param string $default
     * @return array|mixed|null
     */
    public function getRouteRules( string $key = '', string $default = '' )
    {
        return $this->get( "routes.{$key}", $default );
    }

    /**
     * @param string $key
     * @param null $default
     * @return array|mixed|null
     */
    public function getAppConfig( string $key = '', $default = null )
    {
        if ( $key ) {
            return data_get( ( $this->configs[ 'app' ] ?? [] ), $key ) ?: $default;
        }

        return $default;
    }

    /**
     * @param string $key
     * @param null $default
     * @return array|mixed|null
     */
    public function getDatabaseConfig( string $key = '', $default = null )
    {
        return $this->get( 'database.mysql', $default );
    }

    public function all()
    {
        if ( empty( $this->configs ) ) {
            return $this->configs = $this->buildConfig();
        }

        return $this->configs;
    }

    public function get( string $key = '', $default = null )
    {
        if ( $key ) {
            // @see https://laravel.com/docs/master/helpers#method-data-get
            return data_get( $this->configs, $key ) ?: $default;
        }

        return $default;
    }

    /**
     * Class Mapper
     *
     * @param   string      $class
     * @param   null        $default
     * @return  string
     */
    public function classMap( string $class = '', $default = null )
    {
        $classMap = $this->get( 'classMap' );
        return $classMap[ $class ] ?? $class;
    }

    /**
     * @param string $countryCode
     * @return array
     */
    public function getLocationStates( string $countryCode = 'my' )
    {
        if ( !isset( $this->location_states[ $countryCode ] ) ) {

            $states = $this->get( "location-states-{$countryCode}", [] );

            /**
             * Restructure States with Code as Key
             */
            $statesByCode = [];
            foreach ( $states as $stateName => $state ) {
                $state[ 'name' ] = $stateName;
                $statesByCode[ $state[ 'code' ] ] = new LocationState( $state );
            }
            $this->location_states[ $countryCode ][ 'statesByCode' ] = collect( $statesByCode );

            /**
             * Restructure States with Slug as Key
             */
            $statesBySlug = [];
            foreach ( $states as $stateName => $state ) {
                $state[ 'name' ] = $stateName;
                $statesBySlug[ $state[ 'slug' ] ] = new LocationState( $state );
            }
            $this->location_states[ $countryCode ][ 'statesBySlug' ] = collect( $statesBySlug );

            return $this->location_states[ $countryCode ];
        }
        return $this->location_states[ $countryCode ] ?? [];
    }

    /**
     * @param string $code
     * @param string $countryCode
     * @return array
     */
    public function getLocationStateByCode( string $code, string $countryCode = 'my' )
    {
        $locations = $this->getLocationStates( $countryCode );
        return $locations[ 'statesByCode' ]->get( $code );
    }

    /**
     * @param string $slug
     * @param string $countryCode
     * @return array
     */
    public function getLocationStateBySlug( string $slug, string $countryCode = 'my' )
    {
        $locations = $this->getLocationStates( $countryCode );
        return $locations[ 'statesBySlug' ]->get( $slug );
    }
}
