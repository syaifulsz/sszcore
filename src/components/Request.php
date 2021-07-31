<?php

namespace sszcore\components;

/**
 * Request
 * @package sszcore\components
 * @since 0.1.0
 */
class Request extends \Symfony\Component\HttpFoundation\Request
{
    /**
     * Combine $_GET and $_POST parameters
     * @return array
     */
    public function toArray()
    {
        return array_merge( $this->query->all(), $this->request->all() );
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->get( '_route', '' );
    }

    /**
     * @param   string $name
     * @return  bool
     */
    public function isRouteName( string $name )
    {
        return $this->get( '_route' ) === $name;
    }

    /**
     * @return bool
     */
    public static function isHttps()
    {
        $https = ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] !== 'off' );
        if ( !$https ) {
            $baseUri = getenv( 'SITE_BASE_URL' ) ?: '';
            $https = Str::contains( $baseUri, 'https' );
        }
        return $https;
    }
}
