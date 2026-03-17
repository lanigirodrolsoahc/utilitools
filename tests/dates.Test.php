<?php

use PHPUnit\Framework\TestCase;
use Utilitools\Dates;
use Utilitools\DatesStartEndException;

class T_Dates extends TestCase
{
    use Dates;

    public
    function test_secondsDifferential () : void
    {
        $this->assertEquals(
            90,
            $this->diffInSeconds(
                new DateTime('2026-03-16 10:00:00'),
                new DateTime('2026-03-16 10:01:30')
            )
        );
    }

    public
    function test_wrongStartDate () : void
    {
        $this->expectException(DatesStartEndException::class);

        $this->diffInSeconds( 'wrong', new DateTime );
    }

    public
    function test_wrongEndDate () : void
    {
        $this->expectException(DatesStartEndException::class);

        $this->diffInSeconds( new DateTimeImmutable, 'bad' );
    }

    public
    function test_easter () : void
    {
        $this->assertEquals( '2024-03-31', ( self::easterDateTime(2024) )->format(self::$SIMPLE_DATE_FORMAT) );
    }

    public
    function test_nextMonth () : void
    {
        $date = new DateTime('2026-03-15');

        $this->nextMonth($date);

        $this->assertEquals( '2026-04-01', $date->format(self::$SIMPLE_DATE_FORMAT) );
    }

    public
    function test_now () : void
    {
        $this->assertInstanceOf( DateTimeImmutable::class, $this->now() );
    }
}
