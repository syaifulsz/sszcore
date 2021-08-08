<?php

namespace sszcore\components\Console;

use sszcore\traits\SingletonTrait;

/**
 * Class Color
 * @package sszcore\components\Console
 */
class Color
{
    use SingletonTrait;

    const BLACK_NAME                     = 'black';
    const DARK_GRAY_NAME                 = 'dark_gray';
    const BLUE_NAME                      = 'blue';
    const LIGHT_BLUE_NAME                = 'light_blue';
    const GREEN_NAME                     = 'green';
    const LIGHT_GREEN_NAME               = 'light_green';
    const CYAN_NAME                      = 'cyan';
    const LIGHT_CYAN_NAME                = 'light_cyan';
    const RED_NAME                       = 'red';
    const LIGHT_RED_NAME                 = 'light_red';
    const PURPLE_NAME                    = 'purple';
    const LIGHT_PURPLE_NAME              = 'light_purple';
    const BROWN_NAME                     = 'brown';
    const YELLOW_NAME                    = 'yellow';
    const LIGHT_GRAY_NAME                = 'light_gray';
    const WHITE_NAME                     = 'white';
    const MAGENTA_NAME                   = 'magenta';

    const BLACK_FOREGROUND_COLOR         = '0;30';
    const DARK_GRAY_FOREGROUND_COLOR     = '1;30';
    const BLUE_FOREGROUND_COLOR          = '0;34';
    const LIGHT_BLUE_FOREGROUND_COLOR    = '1;34';
    const GREEN_FOREGROUND_COLOR         = '0;32';
    const LIGHT_GREEN_FOREGROUND_COLOR   = '1;32';
    const CYAN_FOREGROUND_COLOR          = '0;36';
    const LIGHT_CYAN_FOREGROUND_COLOR    = '1;36';
    const RED_FOREGROUND_COLOR           = '0;31';
    const LIGHT_RED_FOREGROUND_COLOR     = '1;31';
    const PURPLE_FOREGROUND_COLOR        = '0;35';
    const LIGHT_PURPLE_FOREGROUND_COLOR  = '1;35';
    const BROWN_FOREGROUND_COLOR         = '0;33';
    const YELLOW_FOREGROUND_COLOR        = '1;33';
    const LIGHT_GRAY_FOREGROUND_COLOR    = '0;37';
    const WHITE_FOREGROUND_COLOR         = '1;37';

    const BLACK_BACKGROUND_COLOR         = '40';
    const RED_BACKGROUND_COLOR           = '41';
    const GREEN_BACKGROUND_COLOR         = '42';
    const YELLOW_BACKGROUND_COLOR        = '43';
    const BLUE_BACKGROUND_COLOR          = '44';
    const MAGENTA_BACKGROUND_COLOR       = '45';
    const CYAN_BACKGROUND_COLOR          = '46';
    const LIGHT_GRAY_BACKGROUND_COLOR    = '47';

    private $foregroundColors = [];
    private $backgroundColors = [];

    public function __construct()
    {
        // Set up shell colors
        $this->foregroundColors[ self::BLACK_NAME ]         = self::BLACK_FOREGROUND_COLOR;
        $this->foregroundColors[ self::DARK_GRAY_NAME ]     = self::DARK_GRAY_FOREGROUND_COLOR;
        $this->foregroundColors[ self::BLUE_NAME ]          = self::BLUE_FOREGROUND_COLOR;
        $this->foregroundColors[ self::LIGHT_BLUE_NAME ]    = self::LIGHT_BLUE_FOREGROUND_COLOR;
        $this->foregroundColors[ self::GREEN_NAME ]         = self::GREEN_FOREGROUND_COLOR;
        $this->foregroundColors[ self::LIGHT_GREEN_NAME ]   = self::LIGHT_GREEN_FOREGROUND_COLOR;
        $this->foregroundColors[ self::CYAN_NAME ]          = self::CYAN_FOREGROUND_COLOR;
        $this->foregroundColors[ self::LIGHT_CYAN_NAME ]    = self::LIGHT_CYAN_FOREGROUND_COLOR;
        $this->foregroundColors[ self::RED_NAME ]           = self::RED_FOREGROUND_COLOR;
        $this->foregroundColors[ self::LIGHT_RED_NAME ]     = self::LIGHT_RED_FOREGROUND_COLOR;
        $this->foregroundColors[ self::PURPLE_NAME ]        = self::PURPLE_FOREGROUND_COLOR;
        $this->foregroundColors[ self::LIGHT_PURPLE_NAME ]  = self::LIGHT_PURPLE_FOREGROUND_COLOR;
        $this->foregroundColors[ self::BROWN_NAME ]         = self::BROWN_FOREGROUND_COLOR;
        $this->foregroundColors[ self::YELLOW_NAME ]        = self::YELLOW_FOREGROUND_COLOR;
        $this->foregroundColors[ self::LIGHT_GRAY_NAME ]    = self::LIGHT_GRAY_FOREGROUND_COLOR;
        $this->foregroundColors[ self::WHITE_NAME ]         = self::WHITE_FOREGROUND_COLOR;

        $this->backgroundColors[ self::BLACK_NAME ]         = self::BLACK_BACKGROUND_COLOR;
        $this->backgroundColors[ self::RED_NAME ]           = self::RED_BACKGROUND_COLOR;
        $this->backgroundColors[ self::GREEN_NAME ]         = self::GREEN_BACKGROUND_COLOR;
        $this->backgroundColors[ self::YELLOW_NAME ]        = self::YELLOW_BACKGROUND_COLOR;
        $this->backgroundColors[ self::BLUE_NAME ]          = self::BLUE_BACKGROUND_COLOR;
        $this->backgroundColors[ self::MAGENTA_NAME ]       = self::MAGENTA_BACKGROUND_COLOR;
        $this->backgroundColors[ self::CYAN_NAME ]          = self::CYAN_BACKGROUND_COLOR;
        $this->backgroundColors[ self::LIGHT_GRAY_NAME ]    = self::LIGHT_GRAY_BACKGROUND_COLOR;
    }

    /**
     * @param   string  $string
     * @param   null    $foregroundColor
     * @param   null    $backgroundColor
     * @return  string
     */
    public function color( string $string, $foregroundColor = null, $backgroundColor = null )
    {
        $coloredStr = '';

        // Check if given foreground color found
        if ( isset( $this->foregroundColors[ $foregroundColor ] ) ) {
            $coloredStr .= "\033[" . $this->foregroundColors[ $foregroundColor ] . "m";
        }
        // Check if given background color found
        if ( isset( $this->backgroundColors[ $backgroundColor ] ) ) {
            $coloredStr .= "\033[" . $this->backgroundColors[ $backgroundColor ] . "m";
        }

        // Add string and end coloring
        $coloredStr .=  $string . "\033[0m";

        return $coloredStr;
    }

    public function getForegroundColors()
    {
        return array_keys( $this->foregroundColors );
    }

    public function getBackgroundColors()
    {
        return array_keys( $this->backgroundColors );
    }
}