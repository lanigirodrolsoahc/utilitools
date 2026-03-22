<?php

use PHPUnit\Framework\TestCase;
use Utilitools\Includer;

Includer::Instance()
    ->namespaced()
    ->lazy(false)
    ->setRoot(__DIR__)
    ->files('DummyKrypto.class.php')
    ->inc();

class T_Krypto extends TestCase
{
    private
    const   EVIL    = 'Errare humanum est, perseverare diabolicum.',
            LOREM   = 'Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit...';

    private ?DummyKrypto $krypto;

    /**
     * encrypts lorem ipsum
     *
     * @return  string
     */
    private
    function lorem () : string
    {
        return $this->krypto->crypt(self::LOREM);
    }

    protected
    function setUp () : void
    {
        $this->krypto = DummyKrypto::Instance();
    }

    public
    function test_cypher () : void
    {
        $this->assertNotSame( self::LOREM, $crypted = $this->lorem() );
        $this->assertNotSame( $crypted, $this->krypto->crypt(self::EVIL) );
        $this->assertSame( $nop = '', $this->krypto->uncrypt( $this->krypto->crypt($nop) ) );
    }

    public
    function test_decypher () : void
    {
        $this->assertSame( self::LOREM, $this->krypto->uncrypt( $this->lorem() ) );
    }

    public
    function test_keysGeneration () : void
    {
        $this->assertIsArray( $keys = $this->krypto->keysGen() );
        $this->assertContainsOnly('string', $keys);
    }
}
