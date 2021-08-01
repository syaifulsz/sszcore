<?php

namespace sszcore\models\Component\View;

/**
 * Class Pagination
 * @package sszcore\models\Component\View
 * @since 0.1.7
 *
 * @property bool next_disabled
 * @property string next_url
 * @property bool prev_disabled
 * @property string prev_url
 */
class Pagination extends Model
{
    public $next            = [];
    // public $next_disabled   = false;
    // public $next_url        = '';
    public $prev            = [];
    // public $prev_disabled   = false;
    // public $prev_url        = '';
    public $current         = 0;
    public $pages           = 0;
    public $total           = 0;
    public $list            = 0;
    public $page            = 0;

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'next'              => $this->next,
            'next_disabled'     => $this->next_disabled,
            'next_url'          => $this->next_url,
            'prev'              => $this->prev,
            'prev_disabled'     => $this->prev_disabled,
            'prev_url'          => $this->prev_url,
            'current'           => $this->current,
            'pages'             => $this->pages,
            'total'             => $this->total,
            'list'              => $this->list,
            'page'              => $this->page,
        ];
    }

    public function getNextDisabledAttribute()
    {
        return $this->next[ 'disabled' ] ?? false;
    }

    public function getNextUrlAttribute()
    {
        return $this->next[ 'url' ] ?? '';
    }

    public function getPrevDisabledAttribute()
    {
        return $this->prev[ 'disabled' ] ?? false;
    }

    public function getPrevUrlAttribute()
    {
        return $this->prev[ 'url' ] ?? '';
    }
}