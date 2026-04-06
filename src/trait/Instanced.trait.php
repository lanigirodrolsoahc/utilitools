<?php

namespace Utilitools;


trait Instanced
{
    /**
     *  returns the one and only instance of this class
     *
     * @return  static
    */
    public static
    function & Instance () // : static (PHP8)
    {
        static $instance = null;

        if ( is_null($instance) )
        {
            $class      = get_called_class();
            $instance   = new $class();
        }

        return $instance;
    }
}
