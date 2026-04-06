<?php

namespace Utilitools;

interface Locale
{
    public const DFLT_WEEKEND = ['Sat', 'Sun'];
    public const FR_FR = 'fr_FR';

    /**
     * gets a list of holy days
     *
     * @param   int     $year
     *
     * @return  array<int,string>
     *
     */
    public
    function holidays ( int $year ) : array;

    /**
     * gets a fully described date name
     *
     * @param  \DateTime $date
     *
     * @return  string
     */
    public
    function fullDate ( \DateTime $date ) : string;

    /**
     * gets week end days
     *
     * @return  array<string>
     */
    public
    function weekend () : array;
}
