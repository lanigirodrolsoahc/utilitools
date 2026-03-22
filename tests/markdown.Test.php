<?php

use PHPUnit\Framework\TestCase;
use Utilitools\WorkingDays;
use Utilitools\Includer;

Includer::Instance()
    ->namespaced()
    ->lazy(false)
    ->setRoot(__DIR__)
    ->files('MockMarkDown.class.php')
    ->inc();

class T_MonthlyMarkDown extends TestCase
{
    private WorkingDays $days;

    private MockMarkDown $markdown;

    private array $dates = ['2026.03.16', '2026.03.17'];

    protected
    function setUp () : void
    {
        $this->markdown         = new MockMarkDown;
        $this->days             = new WorkingDays(2026, 3, 1);
        $this->days->fileName   = 'unitTest';
        $this->days->workdays   = $this->dates;
    }

    public
    function test_buildMarkdownContent () : void
    {
        $this->markdown->buildMarkDown( $this->days );

        $this->assertStringContainsString( 'doings', $data = implode( '', $this->markdown->getContent() ) );
        $this->assertStringContainsString($this->markdown::PHP_POWERED, $data);

        array_map(
            function ( string $date ) use ( &$data ) : void
            {
                $this->assertStringContainsString($date, $data);
            },
            $this->dates
        );
    }
}
