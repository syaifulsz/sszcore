<?php

namespace sszcore\components;

/**
 * Class Str
 * @package sszcore\components
 * @since 0.1.0
 */
class Str extends \Illuminate\Support\Str
{
    /**
     * @param string $str
     * @return string
     */
    public static function remove_double_spaces( string $str ) : string
    {
        $str = preg_replace( '!\s+!', ' ', $str );
        return trim( $str );
    }

    /**
     * @param string $str
     * @return string
     */
    public static function remove_alphabet( string $str ) : string
    {
        $str = preg_replace( '/([A-Za-z])\w+/', '', $str );
        return trim( $str );
    }

    /**
     * @param string $str
     * @return string
     */
    public static function remove_dash( string $str ) : string
    {
        return trim( preg_replace( '/\D/', '', $str ) );
    }

    /**
     * @param string|int $number
     * @param int $digit
     * @return string
     */
    public static function number_by_digit( $number, int $digit = 3 ) : string
    {
        return str_pad( $number, $digit, '0', STR_PAD_LEFT );
    }
}
