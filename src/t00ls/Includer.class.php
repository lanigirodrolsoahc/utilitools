<?php

namespace Utilitools;

class Includer
{
    private
    const       MOVE                = '\..',
                UP                  = '..';

    public
    const       EXT_PHP             = 'php',
                TYPE_CLASS          = 'class',
                TYPE_INTERFACE      = 'interface',
                TYPE_TRAIT          = 'trait';

    private     $dirname,
                $files              = [],
                $moves              = [],
                $root;

    private bool $lazy = false;
    public array $maps = [];

    private $namespaced = false;

    private
    function __construct ()
    {
        $this->load();
    }

    /**
     *  returns the one and only instance of this class
     *
     * @return  Includer
    */
    public static
    function & Instance () : Includer
    {
        static $instance = null;

        if ( \is_null($instance) )
        {
            $class      = \get_called_class();
            $instance   = new $class();
        }

        return $instance;
    }

    /**
     * autoload management
     *
     * @param   string  $class
     */
    public
    function autoload ( string $class ) : void
    {
        if ( ! empty($this->maps[$class]) )
            include_once $this->maps[$class];
    }

    /**
     * transforms slashes into backed ones
     *
     * @param   string  $str
     *
     * @return  string
     */
    private
    function backslashes ( string $str ) : string
    {
        return \str_replace('/', '\\', $str);
    }

    /**
     * sets a(n empty ?) list of targeted files
     *
     * @param   array<string>   $list
     *
     * @return  Includer
     */
    public
    function files ( ...$list ) : Includer
    {
        $this->files = $list;

        return $this;
    }

    /**
     * includes all files from current fodler
     *
     * @return  Includer
     */
    public
    function inc () : Includer
    {
        do
        {
            if ( ! \is_dir($path = \implode(DIRECTORY_SEPARATOR, \array_filter( [$this->dirname, ...$this->moves] ))) )
            {
                \error_log( \sprintf('%1$s::%2$s() - unable to identify DIR: %3$s', \get_class($this), __FUNCTION__, $path) );

                break;
            }

            $registry = function ( string $path, string $name ) : void
            {
                $fullPath = \sprintf('%1$s%2$s%3$s', $path, DIRECTORY_SEPARATOR, $name);

                if ( $this->lazy )
                    $this->register($fullPath);
                else
                    include_once $fullPath;
            };

            if ( ! empty($this->files) )
                foreach ( $this->files as $name )
                    $registry($path, $name);
            else
                foreach ( new \DirectoryIterator($path) as $file )
                {
                    if ( $file->isDot() ) continue;

                    $registry( $path, $file->getFilename() );
                }
        }
        while ( 0 );

        return $this->files();
    }

    /**
     * defines lazy mode
     *
     * @param   ?bool   $lazy
     *
     * @return  Includer
     */
    public
    function lazy ( ?bool $lazy = true ) : Includer
    {
        $this->lazy = $lazy;

        return $this;
    }

    /**
     * lsits included files
     *
     * @return  array<string>
     */
    public
    function list () : array
    {
        $list = \get_included_files();

        \sort($list);

        return $list;
    }

    /**
     * sets spl autoload system
     *
     * @return  Includer
     */
    public
    function load () : Includer
    {
        spl_autoload_register([$this, 'autoload']);

        return $this;
    }

    /**
     * determines if includes are namespaced
     *
     * @param   ?string   $namespace
     *
     * @return  Includer
     */
    public
    function namespaced ( ?string $namespace = '' ) : Includer
    {
        $this->namespaced = $namespace ?: false;

        return $this;
    }

    /**
     * registers a file for class map
     *
     * @param   string  $path
     *
     * @return  Includer
     */
    private
    function register ( string $path ) : Includer
    {
        $this->maps[
            \sprintf(
                '%1$s%2$s',
                $this->namespaced
                    ? sprintf('%1$s\\', $this->namespaced)
                    : '',
                \preg_replace(
                    '/\.(class|trait|view)\.php$/',
                    '',
                    basename($path)
                )
            )
        ] = $path;

        return $this;
    }

    /**
     * goes back to root as set by `$this->setRoot()`
     *
     * @return  Includer
     */
    public
    function root () : Includer
    {
        return $this->setRoot( $this->dirname );
    }

    /**
     * sets root for targeting folders
     *
     * @param   string  $dirname
     *
     * @return  Includer
     */
    public
    function setRoot ( string $dirname ) : Includer
    {
        $this->root     = $dirname;
        $this->moves    = \array_filter( \explode('\\', $this->backslashes($dirname)), fn ($value) => $value == '..' );
        $this->dirname  = \substr($dirname, 0, ! ($sub = \strpos($dirname, self::MOVE)) ? \mb_strlen($dirname) : $sub );

        return $this;
    }

    /**
     * goes down in current path
     *
     * @param   string  $folder to attend to
     *
     * @return  Includer
     */
    public
    function to ( string $folder ) : Includer
    {
        $this->moves[] = $this->backslashes($folder);

        return $this;
    }

    /**
     * goes up in current path
     *
     * @param   int     $levels
     *
     * @return  Includer
     */
    public
    function up ( int $levels = 1 ) : Includer
    {
        $this->moves = \array_pad($this->moves, \count($this->moves) + $levels, self::UP);

        return $this;
    }
}
