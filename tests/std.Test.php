<?php

use PHPUnit\Framework\TestCase;
use Utilitools\Std;
use Utilitools\StdNotAnAssociativeArrayException;
use Utilitools\StdNotAValidVariableNameException;

class T_Std extends TestCase
{
    public
    function test_instanciate () : void
    {
        $this->assertInstanceOf( Std::class, Std::__new() );
    }

    public
    function test_attributes () : void
    {
        $this->assertEquals(
            ( $std = Std::__new() )->{ $foo = 'foo' }( $bar = 'bar' )->$foo,
            $bar
        );

        $this->assertEquals(
            $std->{ $fill = 'fill' }( $float = 0.1984 )->$fill,
            $float
        );

        $this->assertEquals(
            $std->{ $array = 'array' }( $data = [__FUNCTION__, __METHOD__] )->$array,
            $data
        );
    }

    public
    function test_setAssociativeArray () : void
    {
        $this->expectException( StdNotAnAssociativeArrayException::class );
        $this->expectExceptionMessage('');

        Std::__new()->__setAll([1]);
    }

    public
    function test_setVariableName () : void
    {
        $this->expectException( StdNotAValidVariableNameException::class );
        $this->expectExceptionMessage('');

        Std::__new()->{'1a'}(1);
    }
}
