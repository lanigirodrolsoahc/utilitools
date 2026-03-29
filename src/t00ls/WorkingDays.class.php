<?php

namespace Utilitools;

class WorkingDays
{
    use Dates;

    private
    const       DAY_FORMAT      = 'd';

    private     $daysCount,
                $weekEnd        = ['Sat', 'Sun'],
                $start;

    public      $fileName,
                $month,
                $workdays       = [], /** array<int, string> */
                $year;


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
        $this->month        = ! $month ? \date('n') : $month;
        $this->year         = \is_int($year) ? $year : \date('Y');
        $this->daysCount    = \cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
        $this->start        = ! $start ? \date('d') : $start;
        $this->fileName     = \sprintf('%1$s%2$s', $this->year, self::stringDigits($this->month));
    }

    /**
     * builds an array listing all working days of current month, starting at current day
     *
     * @return  WorkingDays
     */
    public
    function buildWorkingDays () : WorkingDays
    {
        $this->workdays = [];

        for ( $i = 1; $i <= $this->daysCount; $i++ )
        {
            $date       = \sprintf('%1$s/%2$s/%3$s', $this->year, $this->month, $i);
            $getName    = \date( 'l', \strtotime($date) );
            $dayName    = \substr($getName, 0, 3);

            if ( ! \in_array($dayName, $this->weekEnd) && $i >= $this->start )
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
     *
     * @todo        manage company gifts
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

            if ( ! empty($holidays) )                                       $yearMonthDay[2] = 1;
            if ( ! \array_key_exists($year = $yearMonthDay[0], $holidays) )  $holidays[$year] = $ziss->listHolidays($year)[$year];

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
     * examines if candidate is an int and in range
     *
     * @param   mixed   $candidate
     * @param   int     $minRange
     * @param   int     $maxrange
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
     * lists holidays for french metropole
     *
     * @param   ?int    $year
     *
     * @return  array
     */
    public static
    function listHolidays ( int $year = 0 ) : array
    {
        if ( $year !== 0 )
            $year = (int) (new \DateTime)->format('Y');

        $holidays =
        [
            '1er janvier'           => new \DateTime( \sprintf('%1$s-01-01', $year) ),
            'Fête du travail'       => $mayTheFirst = new \DateTime( \sprintf('%1$s-05-01', $year) ),
            'Victoire des alliés'   => ( clone $mayTheFirst )->add( new \DateInterval('P7D') ),
            'Fête nationale'        => new \DateTime( \sprintf('%1$s-07-14', $year) ),
            'Assomption'            => new \DateTime( \sprintf('%1$s-08-15', $year) ),
            'Toussaint'             => $allSaints = new \DateTime( \sprintf('%1$s-11-01', $year) ),
            'Armistice'             => ( clone $allSaints )->add( new \DateInterval('P10D') ),
            'Noël'                  => new \DateTime( \sprintf('%1$s-12-25', $year) ),
            'Lundi de Pâques'       => $easterMonday = ( self::easterDateTime($year) )->add( new \DateInterval('P1D') ),
            'Ascension'             => ( clone $easterMonday )->add( new \DateInterval('P38D') ),
            'Lundi de Pentecôte'    => ( clone $easterMonday )->add( new \DateInterval('P49D') )
        ];

        \asort($holidays);

        $holidays   = \array_map( fn($date) : string => $date->format(self::$SIMPLE_DATE_FORMAT), $holidays );
        $out        = [];

        foreach ( $holidays as $name => $date )
            $out[ (int) ($date = \explode('-', $date))[0] ][ (int) $date[1] ][ (int) $date[2] ] = $name;

        return $out;
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
