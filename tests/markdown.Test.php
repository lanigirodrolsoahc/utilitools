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

    /**
     * builds monthly markdown
     *
     * @return T_MonthlyMarkDown
     */
    private
    function build () : T_MonthlyMarkDown
    {
        $this->markdown->buildMarkDown( $this->days );

        return $this;
    }

    /**
     * gets generated markdown content
     *
     * @return  string
     */
    private
    function content () : string
    {
        return implode( '', $this->markdown->getContent() );
    }

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
        $this
            ->build()
            ->assertStringContainsString( 'doings', $data = $this->content() );

        $this->assertStringContainsString($this->markdown::PHP_POWERED, $data);

        array_map(
            function ( string $date ) use ( &$data ) : void
            {
                $this->assertStringContainsString($date, $data);
            },
            $this->dates
        );
    }

    public
    function test_offDays () : void
    {
        $this->days->off( [ new \DateTime('2026-03-17') ] );

        $this
            ->build()
            ->assertStringContainsString( 'off 17', $this->content() );
    }
}
