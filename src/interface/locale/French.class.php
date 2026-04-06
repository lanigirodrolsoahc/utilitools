<?php

namespace Utilitools;

class French implements Locale
{
    use Instanced;
    use Dates;

    public
    function holidays ( int $year ) : array
    {
        return [
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
    }

    public
    function fullDate ( \DateTime $date ) : string
    {
        return $this->fullFrenchDate($date);
    }

    public
    function weekend() : array
    {
        return self::DFLT_WEEKEND;
    }
}
