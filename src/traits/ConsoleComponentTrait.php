<?php

namespace sszcore\traits;

use sszcore\components\Console\Color;
use sszcore\components\Str;
use Illuminate\Support\Collection;

/**
 * Trait ConsoleComponentTrait
 * @package sszcore\traits
 *
 * @property string command
 * @property Collection params
 */
trait ConsoleComponentTrait
{
    use ComponentTrait;

    /**
     * @return string
     */
    public function getCommandAttribute()
    {
        if ( !isset( $this->attributes[ 'consoleCommand' ] ) ) {
            $argv = ( $_SERVER[ 'argv' ] ?? [] );
            if ( isset( $argv[ 1 ] ) ) {
                return $this->attributes[ 'consoleCommand' ] = $argv[ 1 ];
            }
        }
        return $this->attributes[ 'consoleCommand' ] ?? '';
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getParamsAttribute()
    {
        if ( !isset( $this->attributes[ 'consoleParams' ] ) ) {

            $consoleParams = [];
            $args = $_SERVER[ 'argv' ] ?? [];

            if ( isset( $args[ 0 ] ) ) {
                unset( $args[ 0 ] );
            }

            if ( isset( $args[ 1 ] ) ) {
                unset( $args[ 1 ] );
            }

            foreach ( $args as $__param ) {
                if ( Str::contains( $__param, '=' ) ) {
                    $param = explode( '=', $__param );
                    $key = str_replace( '--', '', $param[ 0 ] );
                    $consoleParams[ $key ] = $param[ 1 ];
                }
            }

            return $this->attributes[ 'consoleParams' ] = collect( $consoleParams );
        }

        return $this->attributes[ 'consoleParams' ] ?? collect( [] );
    }

    /**
     * @param string $str
     * @param string $strRight
     * @param int $dividerCount
     */
    public function echoDivider( string $str = '', string $strRight = '', int $dividerCount = 100 )
    {
        $strCount = strlen( $str );
        $tail = str_pad( " {$strRight}", $dividerCount - $strCount, '-', STR_PAD_LEFT );
        if ( !$str ) {
            echo $tail . PHP_EOL;
        } else {
            echo $str . ' ' . $tail . PHP_EOL;
        }
    }

    /**
     * @param $str
     * @param string $color
     */
    public function echo( $str, string $color = '' )
    {
        $c = Color::getInstance();

        if ( is_array( $str ) ) {
            print_r( $str );
            echo PHP_EOL;
        } else {
            echo ( $color ? $c->color( $str, $color ) : $str ) . PHP_EOL;
        }
    }

    /**
     * @param string $str
     * @param string $line
     */
    public function echoHeading( string $str, string $line = "=" )
    {
        echo $str . PHP_EOL;
        echo str_repeat( $line, strlen( $str ) ) . PHP_EOL;
    }

    /**
     * @param string $str
     * @param string $horline
     * @param string $vertline
     * @param string $linecolor
     */
    public function echoBox( string $str, string $horline = "-", string $vertline = "|", string $linecolor = 'green' )
    {
        $color = new Color();
        $hor = strlen( $str ) + 4;

        echo $color->color( str_repeat( $horline, $hor ), $linecolor ) . PHP_EOL;
        echo $color->color( "{$vertline} {$str} {$vertline}", $linecolor ) . PHP_EOL;
        echo $color->color( str_repeat( $horline, $hor ), $linecolor ) . PHP_EOL;
    }

    /**
     * @param int $br
     */
    public function echoBreak( int $br = 1 )
    {
        for ( $i = 0; $i < $br ; $i++ ) {
            echo PHP_EOL;
        }
    }

    /**
     * @param int $done
     * @param int $total
     * @param int $size
     */
    public function progressBar( int $done = 0, int $total = 0, int $size = 30 ) {

        // if we go over our bound, just ignore it
        if ( $done < $total) {
            if ( empty( $this->start_time ) ) {
                $this->start_time = time();
            };
            $now = time();

            $perc = (double)( $done / $total );

            $bar = floor( $perc * $size );

            $status_bar = "\r[";
            $status_bar .= str_repeat( "=", $bar );
            if ( $bar < $size ) {
                $status_bar .= ">";
                $status_bar .= str_repeat( " ", $size - $bar );
            } else {
                $status_bar .= "=";
            }

            $disp = number_format( $perc * 100, 0 );

            $status_bar .= "] $disp%  $done/$total";

            $rate = ( $now - $this->start_time ) / $done;
            $left = $total - $done;
            $eta = round($rate * $left, 2);

            $elapsed = $now - $this->start_time;

            // $status_bar.= " ETA: " . number_format( $eta ) . " sec.  elapsed: " . number_format( $elapsed ) . " sec.";

            $this->echo( $status_bar );

            flush();

            // when done, send a newline
            if ( $done == $total ) {
                $this->echoBreak();
            }
        }
    }
}
