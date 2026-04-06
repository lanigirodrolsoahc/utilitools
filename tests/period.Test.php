<?php

use PHPUnit\Framework\TestCase;
use Utilitools\HtmlGenerator;
use Utilitools\Period;
use Utilitools\Dates;

class T_Period extends TestCase
{
    use Dates;

    private const DAYED = 'dayed';
    private const MARCH_16 = 'lundi 16 mars 2026';
    private const PERIOD_ITEM = '<div class="PeriodItem">';
    private const YEAR = '2026';

    /**
     * common builder for test cases
     *
     * @param   string  $start
     * @param   string  $end
     *
     * @return  DOMElement|string
     */
    private
    function builder ( string $start, string $end )
    {
        ob_start();

        ( $doc = new HtmlGenerator )
            ->__add(
                $period = ( new Period )
                    ->starts( $this->dator($start) )
                    ->stops( $this->dator($end) )
                    ->insertableIn($doc->doc)
            )
            ->render($period);

        return ob_get_clean();
    }

    /**
     * creates a date
     *
     * @param  string  $date
     *
     * @return DateTime
     */
    private
    function dator ( string $date ) : DateTime
    {
        return new DateTime( $date, new DateTimeZone($this::$EUROPE_PARIS) );
    }

    /**
     * builds a `div` string representation
     *
     * @param   string      $classes
     * @param   ?string     $title
     *
     * @return  string
     */
    private
    function divor ( string $classes, ?string $title = null ) : string
    {
        return sprintf(
            '<div class="%1$s"%2$s',
            $classes,
            $title === null
                ? ''
                : sprintf(' title="%1$s"', $title)
        );
    }

    public
    function test_renderWithoutCrash () : void
    {
        $this->assertStringContainsString(
            $this->divor('Period'),
            $html = $this->builder('2026-03-01 10:00:00', '2026-03-30 18:00:00')
        );

        $this->assertStringContainsString(self::PERIOD_ITEM, $html);
    }

    public
    function test_dayContainsDate () : void
    {
        $this->assertStringContainsString(
            self::YEAR,
            $html = $this->builder('2026-03-16 09:00:00', '2026-03-16 18:00:00')
        );

        $this->assertStringContainsString( $this->divor(self::DAYED, self::MARCH_16), $html );
    }

    public
    function test_halfDaysDetected () : void
    {
        $this->assertStringContainsString(
            $this->divor(
                'dayed halfedFirst',
                $titled = sprintf('%1$s (demi-journée)',
                self::MARCH_16)
            ),
            $this->builder('2026-03-16 13:00:00', '2026-03-16 18:00:00')
        );

        $this->assertStringContainsString(
            $this->divor('dayed halfedLast', $titled),
            $this->builder('2026-03-16 09:00:00', '2026-03-16 11:30:00')
        );
    }

    public
    function test_multiMonthSinglePeriod () : void
    {
        $this->assertStringContainsString( 'mars 2026', $html = $this->builder('2026-03-28 10:00:00', '2026-04-02 18:00:00') );
        $this->assertStringContainsString('avril 2026', $html);
    }

    public
    function test_storeMultiPeriods () : void
    {
        ob_start();

        ( $doc = new HtmlGenerator )
            ->__add(
                $period = ( new Period )
                    ->starts( $this->dator('2026-03-01 08:30:00') )
                    ->stops( $this->dator('2026-03-20 17:30:00') )
                    ->store()
                    ->starts( $this->dator('2026-05-02 08:30:00') )
                    ->stops( $this->dator('2026-05-15 17:30:00') )
                    ->store()
                    ->insertableIn($doc->doc)
            )
            ->render($period);

        $html = ob_get_clean();

        $this->assertSame( 2, substr_count($html, self::PERIOD_ITEM) );

        $this->assertStringContainsString( $this->divor(self::DAYED, 'vendredi 20 mars 2026' ), $html );
        $this->assertStringContainsString( $this->divor(self::DAYED, 'vendredi 15 mai 2026'), $html );
        $this->assertStringNotContainsString( $this->divor(self::DAYED, 'lundi 18 mai 2026'), $html );
    }

    public function test_holidaysAreExcluded () : void
    {
        $this->assertStringNotContainsString(
            $this->divor(self::DAYED, 'mardi 14 juillet 2026'),
            $this->builder('2026-07-13 08:30:00', '2026-07-15 17:30:00')
        );
    }
}
