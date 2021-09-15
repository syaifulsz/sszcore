<?php

namespace sszcore\components;

use sszcore\traits\ComponentTrait;
use Illuminate\Container\Container;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\MemcachedConnector;
use Illuminate\Contracts\Cache\Repository;
use sszcore\traits\SingletonTrait;

/**
 * Class Cache
 * @package sszcore\components
 * @since 0.1.2
 *
 * @property Cache instance
 * @property Container container
 * @property CacheManager cache_manager
 * @property array cache_config
 * @property Repository cache
 */
class Cache
{
    use ComponentTrait;
    use SingletonTrait;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var CacheManager
     */
    protected $cache_manager;

    /**
     * @var Repository
     */
    public $cache;

    public $prefix = '';
    public $active = true;

    public function __construct( array $configs = [] )
    {
        // project specific properties
        $this->prefix = "{$this->site_id}-{$this->site_env}";

        $this->container = new Container;

        $this->container['config'] = [
            'cache.default' => $configs[ 'driver' ] ?? 'memcached', // `file` or `memcached`
            'cache.stores.file' => [
                'driver' => 'file',
                'path' => "{$this->site_dir}/runtime/cache"
            ],
            'cache.stores.memcached' => [
                'driver' => 'memcached',
                'servers' => [
                    [
                        'host' => $this->getCacheConfig( 'host', '127.0.0.1' ),
                        'port' => $this->getCacheConfig( 'port', 11211 ),
                        'weight' => 100,
                    ],
                ],
            ],
            'cache.prefix' => $this->prefix
        ];
        $this->container[ 'memcached.connector' ] = new MemcachedConnector();

        $this->container[ 'files' ] = new \Illuminate\Filesystem\Filesystem();

        $this->cache_manager = new CacheManager( $this->container );
        $this->cache = $this->cache_manager->store();
    }

    /**
     * @return array
     */
    public function getCacheConfigAttribute()
    {
        if ( !isset( $this->attributes[ 'cache_config' ] ) ) {

            $config = [];
            $appConfigFile = "{$this->app_dir}/configs/memcached.php";
            if ( file_exists( $appConfigFile ) ) {
                $config = require $appConfigFile;
            }

            if ( $this->site_id ) {
                $siteConfig = "{$this->site_dir}/configs/memcached.php";
                if ( file_exists( $siteConfig ) ) {
                    $config = array_replace_recursive( $config, require $siteConfig );
                }
                if ( $this->site_env ) {
                    $envConfigFile = "{$this->site_dir}/configs/{$this->site_env}.php";
                    if ( file_exists( $envConfigFile ) ) {
                        $envConfig = require $envConfigFile;
                        if ( !empty( $envConfig[ 'memcached' ] ) ) {
                            $config = array_replace_recursive( $config, $envConfig[ 'memcached' ] );
                        }
                    }
                }
            }
            return $this->attributes[ 'cache_config' ] = $config;
        }
        return $this->attributes[ 'cache_config' ] ?? [];
    }

    /**
     * @param string $key
     * @return array
     */
    public function getCacheConfig( string $key = '' )
    {
        if ( $key ) {
            return data_get( $this->cache_config, $key );
        }
        return [];
    }

    /**
     * @param $key
     * @param $cacheData
     * @param false $expire
     * @return false|mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setOrGet( $key, $cacheData, $expire = false )
    {
        if ( !$this->active ) {
            return $cacheData;
        }

        if ( is_array( $key ) ) {
            $key = md5( http_build_query( $key ) );
        }

        if ( is_a( $cacheData, 'Closure' ) ) {
            $cacheData = $cacheData();
        }

        if ( !$data = $this->get( $key ) ) {
            $data = $cacheData;
            $this->set( $key, $data, $expire );
        }

        return $data;
    }

    /**
     * @param string $key
     * @param $value
     * @param bool $expire
     * @return bool
     */
    public function set( string $key, $value, bool $expire = false )
    {
        if ( !$this->active ) {
            return true;
        }
        $expire = ( $expire === false ? null : $expire );
        return $this->cache->put( $key, $value, $expire );
    }

    /**
     * @param string $key
     * @return false|mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get( string $key )
    {
        if ( !$this->active ) {
            return false;
        }
        return $this->cache->get( $key );
    }

    /**
     * @param string $key
     * @return bool
     */
    public function remove( string $key )
    {
        if ( !$this->active ) {
            return true;
        }

        return $this->cache->forget( $key );
    }

    /**
     * @return bool
     */
    public function flush()
    {
        if ( !$this->active ) {
            return true;
        }

        return $this->cache->clear();
    }

    /**
     * @return bool
     */
    public function doClearCache()
    {
        return $this->flush();
    }

    /**
     * @return bool
     */
    public static function isRefreshCache()
    {
        return ( isset( $_GET[ 'clearCache' ] ) && ( $_GET[ 'clearCache' ] === 'refresh' ) );
    }
}
