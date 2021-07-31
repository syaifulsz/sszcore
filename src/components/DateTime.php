<?php

namespace sszcore\components;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Class DateTime
 * @package sszcore\components
 */
class DateTime
{
    const MONTH_BY_NAME = [
        'January'   => 1,
        'February'  => 2,
        'March'     => 3,
        'April'     => 4,
        'May'       => 5,
        'June'      => 6,
        'July'      => 7,
        'August'    => 8,
        'September' => 9,
        'October'   => 10,
        'November'  => 11,
        'December'  => 12,
    ];

    /**
     * @param string $monthName
     * @return int|null
     */
    public static function getMonthByName( string $monthName )
    {
        return self::MONTH_BY_NAME[ $monthName ] ?? null;
    }

    /**
     * @param int $year
     * @return array
     */
    public static function listMonthInYear( int $year = 0 )
    {
        $months = [];
        $year = $year ?: (int)date( 'Y' );
        $month = Carbon::create( $year )->firstOfMonth();
        $nextMonth = ( clone $month )->addMonth();
        while ( $month->year === $nextMonth->year ) {
            $months[] = clone $month;
            $month->addMonth();
        }

        return $months;
    }

    /**
     * @param int $year
     * @param string $format
     * @return array
     */
    public static function listMonthInYearCurrent( int $year = 0, string $format = '' )
    {
        $today = Carbon::now()->addMonth();
        $months = [];
        $year = $year ?: (int)date( 'Y' );
        $month = Carbon::create( $year )->firstOfMonth();
        while ( $month->month !== $today->month ) {
            if ( !$format ) {
                $months[] = clone $month;
            } else {
                $months[] = ( clone $month )->format( $format );
            }
            $month->addMonth();
        }

        return $months;
    }

    /**
     * @param int $month
     * @param int $year
     * @return Collection
     */
    public static function listDayInMonth( int $month = 0, int $year = 0 ) : Collection
    {
        $month = $month ?: (int)date( 'm' );
        $year = $year ?: (int)date( 'Y' );
        $days = [];
        $day = Carbon::create( $year, $month )->firstOfMonth();
        $nextMonth = ( clone $day )->addMonth();
        while ( $day->year === $nextMonth->year && $day->month !== $nextMonth->month ) {
            $days[] = clone $day;
            $day->addDay();
        }
        return collect( $days );
    }

    /**
     * Date Range for Current Month
     *
     * @return array
     */
    public static function dateRangeMonth() : array
    {
        return [
            date( 'Y-m-d', strtotime( 'first day of this month' ) ),
            date( 'Y-m-d', strtotime( 'first day of next month' ) )
        ];
    }

    /**
     * Date Range for Previous Month
     *
     * @return array
     */
    public static function dateRangeMonthPrev() : array
    {
        return [
            date( 'Y-m-d', strtotime( 'first day of last month' ) ),
            date( 'Y-m-d', strtotime( 'first day of this month' ) )
        ];
    }
}
