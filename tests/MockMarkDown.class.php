<?php

use Utilitools\MonthlyMarkDown;
use Utilitools\WorkingDays;

class MockMarkDown extends MonthlyMarkDown
{
    protected
    function createFile ( WorkingDays $wd ) : bool
    {
        return true;
    }

    /**
     * gets content for test purposes
     *
     * @return  array
     */
    public
    function getContent () : array
    {
        return $this->markDown;
    }
}
