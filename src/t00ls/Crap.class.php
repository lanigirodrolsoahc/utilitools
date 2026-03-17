<?php

namespace Utilitools;

class Crap
{
    /**
     * give a 's' if needed
     *
     * @param   int|string|array    $what
     *
     * @return  null|string
     */
    public static
    function esse ( $what )
    {
        if ( \is_string($what) && \ctype_digit($what) )
            $what = \intval($what);
        elseif ( \is_array($what) )
            $what = \count($what);

        return $what < 2 ? null : 's';
    }

    /**
     * htmlentities
     *
     * @param   string  $str
     *
     * @return  string
     */
    public static
    function he ( string $str ) : string
    {
        return \htmlentities($str);
    }

    /**
     * check if environment is a developpment one
     *
     * @return  bool
     */
    public static
    function isDev () : bool
    {
        return empty($_SERVER['HTTP_HOST']) ? false : \preg_match('/localhost|127\.0\.0\.1/i', $_SERVER['HTTP_HOST']);
    }

    /**
     * logs anything
     *
     * @param   mixed   $anything
     *
     * @return  Crap
     */
    public static
    function log ( ...$anything ) : Crap
    {
        foreach ( $anything as $thing )
            \error_log( \json_encode($thing, JSON_PRETTY_PRINT) );

        return new static;
    }

    /**
     * removes execution time limit
     */
    public static
    function noTimeLimit () : void
    {
        \ini_set('max_execution_time', 0);
    }

    /**
     * removes accentuated characters from given string
     *
     * @param   string  $str
     *
     * @return  string
     */
    public static
    function removeAccents ( string $str ) : string
    {
        return \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')->transliterate($str);
    }

    /**
     * removes extra space in strings
     *
     * @param   string|array   $str
     *
     * @return  string|array
     */
    public static
    function removeExtraSpace ( $str )
    {
        return \preg_replace('/\s+/s', ' ', $str);
    }

    /**
     * recursively trims any string contained in given data
     *
     * @param   mixed   &$datas
     *
     * @return  void
     */
    public static
    function TrimWalk ( &...$datas ) : void
    {
        foreach ( $datas as &$data )
        {
            if ( \is_string($data) )
                $data = \trim($data);

            elseif ( \is_array($data) || \is_object($data) )
                foreach ( $data as &$value )
                    self::TrimWalk($value);
        }
    }

    /**
     * escapes a string for sprintf's use
     *
     * @param   string  $str
     *
     * @return  string
     */
    public static
    function escapeSprintF ( string $str ) : string
    {
        return \str_replace('%', '%%', $str);
    }
}
