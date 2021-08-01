<?php

namespace sszcore\models\Component\View;

use Illuminate\Support\Collection;

/**
 * Class Manager
 * @package sszcore\models\Component\View
 * @since 0.1.7
 *
 * @property int parameterShowingFrom
 * @property int parameterShowingTo
 * @property int parameterCurrentShowingFrom
 * @property int parameterCurrentShowingTo
 * @property int parameterNextShowingFrom
 * @property int parameterNextShowingTo
 * @property int parameterPrevShowingFrom
 * @property int parameterPrevShowingTo
 */
class Manager extends Model
{
    public $parameterKeyword;
    public $parameterPaged;
    public $parameterTotalAvailable;
    public $parameterTotalShowing;
    public $parameterPagesAvailable;
    public $parameterOffset;
    public $parameterLimit;

    /**
     * @return int
     */
    public function getParameterShowingFromAttribute()
    {
        return $this->parameterOffset + 1;
    }

    /**
     * @return int
     */
    public function getParameterShowingToAttribute()
    {
        return $this->parameterLimit * $this->parameterPaged;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'parameterKeyword'              => $this->parameterKeyword,
            'parameterPaged'                => $this->parameterPaged,
            'parameterTotalAvailable'       => $this->parameterTotalAvailable,
            'parameterTotalShowing'         => $this->parameterTotalShowing,
            'parameterPagesAvailable'       => $this->parameterPagesAvailable,
            'parameterOffset'               => $this->parameterOffset,
            'parameterLimit'                => $this->parameterLimit,
            'parameterShowingFrom'          => $this->parameterShowingFrom,
            'parameterShowingTo'            => $this->parameterShowingTo,
            'parameterCurrentShowingFrom'   => $this->parameterCurrentShowingFrom,
            'parameterCurrentShowingTo'     => $this->parameterCurrentShowingTo,
            'parameterNextShowingFrom'      => $this->parameterNextShowingFrom,
            'parameterNextShowingTo'        => $this->parameterNextShowingTo,
            'parameterPrevShowingFrom'      => $this->parameterPrevShowingFrom,
            'parameterPrevShowingTo'        => $this->parameterPrevShowingTo,
        ];
    }

    /**
     * @var Collection
     */
    public $queryItems;

    /**
     * @var Pagination
     */
    public $pagination;

    /**
     * @param array $attributes
     */
    public function setAttributes( array $attributes )
    {
        parent::setAttributes( $attributes );
        if ( !empty( $attributes[ 'pagination' ] ) ) {
            $pagination = new Pagination();
            $pagination->setAttributes( $attributes[ 'pagination' ] );
            $this->pagination = $pagination;
        }
    }

    /**
     * @return int
     */
    public function getParameterCurrentShowingFromAttribute()
    {
        return $this->parameterShowingFrom;
    }

    /**
     * @return int
     */
    public function getParameterCurrentShowingToAttribute()
    {
        return $this->parameterShowingTo;
    }

    /**
     * @return int
     */
    public function getParameterNextShowingFromAttribute()
    {
        return $this->parameterShowingFrom + $this->parameterLimit;
    }

    /**
     * @return int
     */
    public function getParameterNextShowingToAttribute()
    {
        return $this->parameterShowingTo + $this->parameterLimit;
    }

    /**
     * @return int
     */
    public function getParameterPrevShowingFromAttribute()
    {
        $prevFrom = 0;
        $pf = $this->parameterShowingFrom - $this->parameterLimit;
        if ( $pf > 0 ) {
            $prevFrom = $pf;
        }
        return $prevFrom;
    }

    /**
     * @return int
     */
    public function getParameterPrevShowingToAttribute()
    {
        $prevTo = 0;
        $pn = $this->parameterShowingTo - $this->parameterLimit;
        if ( $pn > 0 ) {
            $prevTo = $pn;
        }
        return $prevTo;
    }
}