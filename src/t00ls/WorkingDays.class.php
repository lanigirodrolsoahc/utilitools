<?php

namespace Utilitools;

use DateTime;

class WorkingDays
{
    use Dates;

    private
    const       DAY_FORMAT      = 'd';

    private ?int $daysCount;
    private ?int $start;
    private array $offDays = []; /** array<int> */
    private array $weekEnd = [];
    public ?string $fileName;
    public ?int $month;
    public array $workdays = []; /** array<int, string> */
    public ?int $year;


    /**
     * @param   mixed   $year       has to be an int to be used
     * @param   mixed   $month      has to be an int to be used, 1 <= $month <= 12
     * @param   mixed   $start      has to be an int to be used, 1 <= $day <= 31
     */
    public
    function __construct ( $year = null, $month = null, $start = null )
    {
        $month              = $this->isIntInValidRange($month, 1, 12);
        $start              = $this->isIntInValidRange($start, 1, 31);

        $this->month        = ! $month ? \intval(\date('n')) : $month;
        $this->year         = \is_int($year) ? $year : \intval(\date('Y'));
        $this->daysCount    = \cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
        $this->start        = ! $start ? \intval(\date(self::DAY_FORMAT)) : $start;
        $this->fileName     = \sprintf('%1$s%2$s', $this->year, self::stringDigits($this->month));
        $this->weekEnd      = $this->getLocale()->weekend();
    }

    /**
     * builds an array listing all working days of current month, starting at current day
     *
     * @param   ?bool   $includeNonWorkingDays
     *
     * @return  WorkingDays
     */
    public
    function buildWorkingDays ( ?bool $includeNonWorkingDays = true ) : WorkingDays
    {
        $this->workdays = [];

        $zeroTime = fn ( \DateTime $day ) : \DateTime => ( clone $day )->setTime(0, 0, 0);

        $holidays = \array_map(
            fn ( \DateTime $day ) : \DateTime => $zeroTime($day),
            \array_merge(
                $this->offDays,
                $this->getLocale()->holidays($this->year)
            )
        );

        for ( $i = 1; $i <= $this->daysCount; $i++ )
        {
            $date       = \sprintf('%1$s/%2$s/%3$s', $this->year, $this->month, $i);
            $getName    = \date( 'l', \strtotime($date) );
            $dayName    = \substr($getName, 0, 3);

            if (
                ! \in_array($dayName, $this->weekEnd)
                && $i >= $this->start
                && (
                    $includeNonWorkingDays
                    ?: ! \in_array(
                        $zeroTime( new DateTime( \sprintf('%1$s-%2$02d-%3$02d', $this->year, $this->month, $i) ) ),
                        $holidays
                    )
                )
            )
                $this->workdays[$i] = \sprintf('%1$s.%2$s.%3$s - %4$s', $this->year, self::stringDigits($this->month), self::stringDigits($i), $getName);
        }

        return $this;
    }

    /**
     * calculates worked days for given period of time
     *
     * @param       \DateTime       $start
     * @param       \DateTime       $end
     *
     * @return      int
     */
    public
    function calcWorkedDays ( \DateTime $start, \DateTime $end ) : int
    {
        $current        = clone $start;
        $end            = clone $end;
        $worked         = 0;
        $holidays       = [];
        $endYearMonth   = $end->format(self::$YEAR_MONTH_FORMAT);

        while ( ($currentMonth = $current->format(self::$YEAR_MONTH_FORMAT)) <= $endYearMonth )
        {
            $yearMonthDay   = \array_map( fn ($str) => (int) $str, explode('-', $current->format(self::$SIMPLE_DATE_FORMAT)) );
            $workedDays     = ( $ziss = new self(...$yearMonthDay) )->buildWorkingDays()->workdays;
            $isFirstMonth   = ! isset($isLastMonth);
            $isLastMonth    = $currentMonth == $endYearMonth;

            if ( ! empty($holidays) )
                $yearMonthDay[2] = 1;

            if ( ! \array_key_exists($year = $yearMonthDay[0], $holidays) )
                $holidays[$year] = $ziss->listHolidays($year)[$year];

            $monthHolidays = $holidays[ $year ][ $yearMonthDay[1] ] ?? false;

            if ( $isLastMonth )
                $workedDays = \array_filter($workedDays, fn($value) => new \DateTime(\str_replace('.', '-', \substr($value, 0, 10))) <= $end );

            if ( $monthHolidays )
            {
                if ( $isFirstMonth )
                    $monthHolidays = \array_filter($monthHolidays, fn ($key) => $key >= $current->format(self::DAY_FORMAT), ARRAY_FILTER_USE_KEY);
                if ( $isLastMonth )
                    $monthHolidays = \array_filter($monthHolidays, fn ($key) => $key <= $end->format(self::DAY_FORMAT), ARRAY_FILTER_USE_KEY);

                foreach ( $workedDays as $day )
                    if ( ! empty($monthHolidays[ (int) \substr($day, 8, 2) ]) )
                        $worked--;
            }

            $worked += \count($workedDays);

            $ziss->nextMonth($current);
        }

        return $worked;
    }

    /**
     * gets `Locale` instance as set in `System`, or default one
     *
     * @return  Locale
     */
    private
    function getLocale () : Locale
    {
        return System::Instance()->{ System::LOCALE } ?? French::Instance();
    }

    /**
     * examines if candidate is an int and in range
     *
     * @param   mixed   $candidate
     * @param   int     $minRange
     * @param   int     $maxRange
     *
     * @return  int|false
     */
    private
    function isIntInValidRange ( $candidate, int $minRange, int $maxRange )
    {
        do
        {
            if ( ! \is_int($candidate) ) break;

            $candidate = filter_var($candidate, FILTER_VALIDATE_INT, ['options' => ['min_range' => $minRange, 'max_range' => $maxRange]]);

            if ( ! $candidate  ) break;

            return $candidate;
        }
        while ( 0 );

        return false;
    }

    /**
     * lists holidays based on `Locale` (see interface)
     *
     * @param   ?int    $year
     *
     * @return  array
     */
    public
    function listHolidays ( int $year = 0 ) : array
    {
        if ( $year === 0 )
            $year = (int) (new \DateTime)->format('Y');

        $holidays = $this->getLocale()->holidays($year);

        foreach ( $this->offDays as $day )
            $holidays[ \sprintf('off %1$02d', $day->format(self::DAY_FORMAT) ) ] = $day;

        \asort($holidays);

        $holidays   = \array_map( fn ($date) : string => $date->format(self::$SIMPLE_DATE_FORMAT), $holidays );
        $out        = [];

        foreach ( $holidays as $name => $date )
            $out[ (int) ($date = \explode('-', $date))[0] ][ (int) $date[1] ][ (int) $date[2] ] = $name;

        return $out;
    }

    /**
     * defines off days for current month
     *
     * @param   array<\DateTime>  $days
     *
     * @return  WorkingDays
     */
    public
    function off ( array $days ) : WorkingDays
    {
        $this->offDays = \array_unique( \array_filter( $days, fn ( $day ) : bool => $day instanceof \DateTime ) );

        return $this;
    }

    /**
     * adds leading zeros to an int
     *
     * @param   int     $digit      the int to be leaded
     * @param   int     $digInStr   the number of digits in returned string
     *
     * @return  string
     */
    public static
    function stringDigits ( int $digit, int $digInStr = 2 ) : string
    {
        return \sprintf(\sprintf('%%0%1$sd', $digInStr), $digit);
    }
}
