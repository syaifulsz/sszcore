<?php

namespace sszcore\traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Trait ManagerTrait
 * @package sszcore\traits
 * @since 0.2.0
 *
 * @property string     parameter_keyword
 * @property string     parameter_paged
 * @property bool       query_cache_enabled
 * @property array      query_cache_key
 */
trait ManagerTrait
{
    public $parameter_total_available = 0;
    public $parameter_total_showing = 0;
    public $parameter_limit = 10;
    public $parameter_offset = 0;
    public $parameter_pages_available = 0;
    public $query_items;

    /**
     * @var Builder
     */
    public $query_service;

    /**
     * @TODO Set cache for Manager
     * @return bool
     */
    public function getQueryCacheEnabledAttribute()
    {
        return false;
    }

    /**
     * @return array
     */
    public function getQueryCacheKeyAttribute()
    {
        $reflection = new \ReflectionClass( $this );
        return [ $reflection->getNamespaceName() ];
    }

    /**
     * @return string
     */
    public function getParameterKeywordAttribute()
    {
        if ( !isset( $this->attributes[ 'parameter_keyword' ] ) ) {
            return $this->attributes[ 'parameter_keyword' ] = $this->request->get( 'keyword', '' );
        }

        return $this->attributes[ 'parameter_keyword' ] ?? '';
    }

    /**
     * @return integer
     */
    public function getParameterPagedAttribute()
    {
        if ( !isset( $this->attributes[ 'parameter_paged' ] ) ) {
            return $this->attributes[ 'parameter_paged' ] = (int)$this->request->get('page', 1 );
        }

        return $this->attributes[ 'parameter_paged' ] ?? 1;
    }

    /**
     * @return Builder|null
     */
    public function model()
    {
        return null;
    }

    /**
     * @return array
     */
    public function queryKeywords()
    {
        return [ 'keywords', 'like', "%{$this->parameter_keyword}%" ];
    }

    /**
     * @param array $config
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function query( array $config = [] )
    {
        $query = [];
        $service = $this->model();

        if ( $this->parameter_keyword ) {
            $query = array_merge( $query, [ $this->queryKeywords() ] );
        }
        $query = array_merge( $query, $config );
        $service->where( $query );

        $this->parameter_total_available = $service->get()->count();
        $this->parameter_pages_available = $this->parameter_total_available ? ceil( $this->parameter_total_available / $this->parameter_limit ) : 0;
        $this->parameter_offset = ( $this->parameter_paged <= 1 ? 0 : ( $this->parameter_paged - 1 ) ) * $this->parameter_limit;

        $this->query_service = $this
            ->afterQuery(
                $service
                    ->where( $query )
                    ->offset( $this->parameter_offset )
                    ->limit( $this->parameter_limit )
            );

        $this->query_items = $this->query_service->get();

        $this->parameter_total_showing = count( $this->query_items );

        return $this->query_items;
    }

    /**
     * @param array $config
     * @return \Illuminate\Database\Eloquent\Collection|Collection
     */
    public function queryNoPaged( array $config = [] )
    {
        $query = [];
        $service = $this->model();

        if ( $this->parameter_keyword ) {
            $query = array_merge( $query, [ $this->queryKeywords() ] );
        }
        $query = array_merge( $query, $config );
        $service->where( $query );

        $this->parameter_total_available = $service->get()->count();
        $this->parameter_pages_available = 1;
        $this->parameter_offset = 0;

        $this->query_items = $this
            ->afterQuery(
                $service
                    ->where( $query )
            )
            ->get();

        $this->parameter_total_showing = count( $this->query_items );

        return $this->query_items;
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function afterQuery( $query )
    {
        return $query->latest();
    }



    /**
     * @param array $config
     * @param bool $noPaged
     * @return array
     */
    public function listing( array $config = [], bool $noPaged = false )
    {
        if ( $noPaged ) {
            $this->queryNoPaged( $config );
        } else {
            $this->query( $config );
        }

        $output = [
            'parameterKeyword' => $this->parameter_keyword,
            'parameterPaged' => $this->parameter_paged,
            'parameterTotalAvailable' => $this->parameter_total_available,
            'parameterTotalShowing' => $this->parameter_total_showing,
            'parameterPagesAvailable' => $this->parameter_pages_available,
            'queryItems' => $this->query_items,
            'pagination' => $this->view->makePagination(
                null,
                $this->parameter_paged,
                $this->parameter_total_showing,
                $this->parameter_pages_available,
                $this->parameter_total_available
            )
        ];

        $this->view->addParams( $output );
        return $output;
    }
}
