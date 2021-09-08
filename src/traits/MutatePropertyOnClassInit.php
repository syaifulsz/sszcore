<?php

namespace sszcore\traits;

/**
 * Trait MutatePropertyOnClassInit
 * @since 0.2.0
 */
trait MutatePropertyOnClassInit
{
    public $initMutateProperties = [];
    public $mutateProperties = [];

    /**
     * Initiate Mutated Properties on Controller Construct
     */
    public function initMutateProperty()
    {
        foreach ( $this->initMutateProperties as $property ) {
            $this->mutateProperties[ $property ] = $this->{$property};
        }
        $this->view->addParams( [
            'mutateProperties' => $this->mutateProperties
        ] );
    }
}