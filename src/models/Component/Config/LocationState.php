<?php

namespace sszcore\models\Component\Config;

use Illuminate\Support\Collection;

/**
 * Class LocationState
 * @package sszcore\models\Component\Config
 * @since 0.1.2
 */
class LocationState extends Collection
{
    public $name;
    public $name_alt;
    public $slug;
    public $code;

    public function __construct( $items = [] )
    {
        parent::__construct( $items );

        $this->name = $items[ 'name' ] ?? '';
        $this->name_alt = $items[ 'name_alt' ] ?? '';
        $this->slug = $items[ 'slug' ] ?? '';
        $this->code = $items[ 'code' ] ?? '';
    }
}