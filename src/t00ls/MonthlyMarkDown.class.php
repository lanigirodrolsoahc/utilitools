<?php

namespace Utilitools;

class MonthlyMarkDownException extends \Exception {}

class MonthlyMarkDown
{
    const   CHECK_BOX       = '* [ ]',
            CHECKS_SYMBOLS  = '> (✔️||✅||❌||🚧||〽️||➗||⛔||💡||❔||🤐||⚠️||ℹ️||👈) # coches & utils',
            CLI_BREAK       = "\n",
            FILE_TITLE      = 'doings',
            FOLDER          = 'data',
            IMG_INSERT      = '> ![🗨](images/🗨.png \'🗨\') # img insertion',
            LINE_BREAK      = "\r\n",
            LINK_TO_FILE    = '> [linkText](./subFolderToCurrentFile/fileName)',
            LINK_ABSOLUTE   = '> [linkText](<file:///d:/absolute.md>)',
            PHP_POWERED     = '> Powered by 🐘',
            SEPARATOR       = '---',
            SLEEPY          = '💤',
            TITLE_SYMB      = '#';

    private $holidays       = [],
            $monthName;

    protected $markDown       = [];

    /**
     * builds a day as a set of rows for Mardown file, stores it in `$this->markDown`
     *
     * @param   string      $day
     * @param   ?string     $holiday
     */
    public
    function buildDay ( string $day, string $holiday = '' ) : void
    {
        $this->markDown[] = \vsprintf(
            '%1$s%1$s %2$s%3$s%3$s%4$s%3$s%3$s%5$s%3$s%3$s',
            [
                self::TITLE_SYMB,
                $day,
                self::LINE_BREAK,
                empty($holiday)
                    ? self::CHECK_BOX
                    : \sprintf('- %1$s %2$s', self::SLEEPY, $holiday),
                self::SEPARATOR
            ]
        );
    }

    /**
     * builds `$this->markDown`
     *
     * @param   WorkingDays     $wd
     *
     * @throws  MonthlyMarkDownException
     */
    public
    function buildMarkDown ( WorkingDays $wd )
    {
        if ( empty( $this->holidays[ $wd->year ] ?? false ) )
            $this->holidays[ $wd->year ] = WorkingDays::listHolidays()[ $wd->year ] ?? [];

        $wd->buildWorkingDays();
        $this->monthName = $this->monthToString($wd);
        $this->buildTitle($wd);

        $this->markDown[] = \sprintf('<font style="font-family: cursive">%1$s%1$s', self::LINE_BREAK);

        foreach ( $wd->workdays as $day )
        {
            $found      = \preg_match('/(?:[0-9]{4}\.[0-9]{2}\.)([0-9]{2})/', $day, $match);
            $holiday    = ( $found && ($txt = $this->holidays[ (int) $wd->year ][ (int) $wd->month ][ (int) $match[1] ] ?? false) ) ? $txt : null;

            $this->buildDay($day, $holiday ?? '');
        }

        $this->markDown[] = \sprintf('</font>%1$s', self::LINE_BREAK);

        if ( ! $this->createFile($wd) )
            throw new MonthlyMarkDownException('Unable to create file.');
    }

    /**
     * builds an opening for Mardown file
     *
     * @param   WorkingDays     $wd
     */
    private
    function buildTitle ( WorkingDays $wd ) : void
    {
        $this->markDown[] = \vsprintf(
            '%1$s %2$s %3$s %4$s%5$s%5$s',
            [
                self::TITLE_SYMB,
                self::FILE_TITLE,
                $this->monthName,
                $wd->year,
                self::LINE_BREAK
            ]
        );

        $this->markDown[] = (
            $sprintor = fn ( array $items ) : string => \vsprintf('%1$s %2$s', $items)
        )(
            [
                self::CHECKS_SYMBOLS,
                self::LINE_BREAK
            ]
        );

        $this->markDown[] = $sprintor(
            [
                self::IMG_INSERT,
                self::LINE_BREAK
            ]
        );

        $this->markDown[] = $sprintor(
            [
                self::LINK_TO_FILE,
                self::LINE_BREAK
            ]
        );

        $this->markDown[] = $sprintor(
            [
                self::LINK_ABSOLUTE,
                self::LINE_BREAK
            ]
        );

        $this->markDown[] = vsprintf(
            '%1$s %2$s%2$s',
            [
                self::PHP_POWERED,
                self::LINE_BREAK
            ]);
    }

    /**
     * creates a file inside appropriate folders iff not already existing
     *
     * @param   WorkingDays     $wd
     *
     * @return  bool
     */
    protected
    function createFile ( WorkingDays $wd ) : bool
    {
        $doings         = $this->createFolder( \sprintf('../%1$s/%2$s', self::FOLDER, self::FILE_TITLE) );
        $yeared         = $this->createFolder( \sprintf('%1$s/%2$s', $doings, $wd->year) );
        $file           = \sprintf('%1$s/%2$s%3$s.md', $yeared, self::FILE_TITLE, $wd->fileName);

        self::createFolder( \sprintf('%1$s/images', $yeared) );
        self::createFolder( \sprintf('%1$s/files', $yeared) );
        self::createFolder( \sprintf('%1$s/markdownFiles', $yeared) );
        self::createFolder( \sprintf('%1$s/markdownFiles/images', $yeared) );
        self::createFolder( \sprintf('%1$s/sql', $yeared) );
        self::createFolder( \sprintf('%1$s/mindMaps', $yeared) );

        return ! \file_exists($file)
            ? \file_put_contents( $file, \implode($this->markDown) ) !== false
            : false;
    }

    /**
     * creates a folder iff not existing
     *
     * @param   string  $path
     *
     * @return  string  $path
     */
    public static
    function createFolder ( string $path ) : string
    {
        if ( ! \is_dir($path) )
            \mkdir($path);

        return $path;
    }

    /**
     * converts an int month to a lowercase string month (ENG)
     *
     * @param   WorkingDays     $wd
     *
     * @return  string
     */
    private
    function monthToString ( WorkingDays $wd ) : string
    {
        return mb_strtolower( ( \DateTime::createFromFormat('!m', $wd->month) )->format('F') );
    }
}
