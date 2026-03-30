<?php

namespace Utilitools;

class DatesStartEndException extends \Exception {}

trait Dates
{
    public static string $EUROPE_PARIS                  = 'Europe/Paris';
    public static string $FIFTEEN_MINUTES_INTERVAL      = 'PT15M';
    public static string $FILE_DATE_FORMAT              = 'Ymd';
    public static string $FILE_DATE_FULL_FORMAT         = 'YmdHis';
    public static string $ONE_DAY_INTERVAL              = 'P1D';
    public static int $SECONDS_IN_DAY                   = 86400;
    public static string $SIMPLE_DATE_FORMAT            = 'Y-m-d';
    public static string $SIMPLE_FRENCH_DATE_FORMAT     = 'd-m-Y';
    public static string $THIRTY_MINUTES_INTERVAL       = 'PT30M';
    public static string $TIMEZONE                      = 'Europe/Paris';
    public static string $TIMESTAMP_FORMAT              = 'Y-m-d H:i:s';
    public static string $YEAR_FORMAT                   = 'Y';
    public static string $YEAR_MONTH_FORMAT             = 'Y-m';

    /**
     * calculates difference in seconds between two dates
     *
     * @param   \DateTimeImmutable|\DateTime    $start
     * @param   \DateTimeImmutable|\DateTime    $end
     *
     * @return  int
     *
     * @throws  DatesStartEndException
     */
    public
    function diffInSeconds ( $start, $end ) : int
    {
        if ( ! ($start instanceof \DateTimeImmutable) && ! ($start instanceof \DateTime) )  throw new DatesStartEndException('start');
        if ( ! ($end instanceof \DateTimeImmutable) && ! ($end instanceof \DateTime) )      throw new DatesStartEndException('end');

        return ( $interval = $start->diff($end) )->days * 86400 + $interval->h * 3600 + $interval->i * 60 + $interval->s;
    }

    /**
     * returns Easter DateTime
     *
     * @param   int     $year
     *
     * @return  \DateTime
     */
    public static
    function easterDateTime ( int $year ) : \DateTime
    {
        return ( new \DateTime(
            \sprintf('%1$s-03-21', $year),
            new \DateTimeZone(self::$EUROPE_PARIS)
        ) )->add( new \DateInterval(\sprintf('P%1$sD', \easter_days($year))));
    }

    /**
     * gets a year and a month
     *
     * @param   \DateTimeInterface  $date
     * @param   ?string             $locale
     * @param   ?string             $timezone
     *
     * @return  string
     */
    public
    function fullFrenchDate ( \DateTimeInterface $date, string $locale = 'fr', string $timezone = '' ) : string
    {
        return (
            new \IntlDateFormatter(
                $locale,
                \IntlDateFormatter::FULL,
                \IntlDateFormatter::NONE,
                $timezone ?: self::$EUROPE_PARIS
            )
        )->format($date);
    }

    /**
     * goes to first day of next month
     *
     * @param   \DateTime    &$date
     */
    public
    function nextMonth ( \DateTime &$date ) : void
    {
        $reference = ( clone $date )->format('m');

        while ( $date->format('m') == $reference ) $date->add( new \DateInterval(self::$ONE_DAY_INTERVAL) );
    }

    /**
     * gives DateTimeImmutable of current time
     *
     * @return  \DateTimeImmutable
     */
    public
    function now () : \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone(self::$TIMEZONE));
    }

    /**
     * gives a pretty representation of given date
     *
     * @param   string      $date
     * @param   ?string     $fmt
     *
     * @return  string
     */
    public
    function strToPretty ( string $date, string $fmt = 'fr_FR' ) : string
    {
        return ( new \IntlDateFormatter($fmt, \IntlDateFormatter::LONG, \IntlDateFormatter::NONE) )->format( \strtotime(\explode(' ', $date, 2)[0] ));
    }
}
