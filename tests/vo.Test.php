<?php

use PHPUnit\Framework\TestCase;
use Utilitools\Database;
use Utilitools\System;
use Utilitools\Includer;
use Utilitools\Std;

System::Instance()
    ->register(
        Std::__new()
            ->{ System::SQLite_FILE }( sprintf('%1$s/sqllitefile.sqlite', __DIR__) )
    );

Includer::Instance()
    ->lazy(false)
    ->setRoot(__DIR__)
    ->files('DummyVO.class.php')
    ->inc();

class T_VirtualObject extends TestCase
{
    private ?DummyVO $dummy;

    /**
     * destroys sqlite file if needed
     *
     * @return void
     */
    private static
    function clearFile () : void
    {
        if ( is_file( $file = System::Instance()->{ System::SQLite_FILE } ) )
            unlink($file);
    }

    public static
    function setUpBeforeClass () : void
    {
        self::clearFile();
    }

    protected
    function setUp () : void
    {
        ( $this->dummy = DummyVO::Instance() )->createTable();
    }

    public static
    function tearDownAfterClass () : void
    {
        Database::Instance()->kill();

        self::clearFile();
    }

    public
    function test_lifeCycle () : void
    {
        $this->assertInstanceOf(
            Std::class,
            $std = $this->dummy->init()->getVirtual()
        );

        $this->assertInstanceOf(
            Std::class,
            $this->dummy
                ->fill(
                    $kant = clone $std
                        ->{ $fid = $this->dummy->f_id }( $first = 1 )
                        ->{ $fname = $this->dummy->f_name }( $name = 'Immanuel' )
                        ->{ $frole = $this->dummy->f_role }( $role = 'philosopher' )
                )
                ->save()
        );

        $this->assertInstanceOf(
            Std::class,
            $this->dummy
                ->fill(
                    $std
                        ->$fid(2)
                        ->$fname('Socrate')
                        ->$frole('corrupter')
                )
                ->save()
        );

        $this->assertInstanceOf(
            \stdClass::class,
            $result = $this->dummy->is($first)
        );

        $this->assertSame( $name, $result->$fname );
        $this->assertSame( $role, $result->$frole );

        $this->assertInstanceOf(
            Std::class,
            $this->dummy->getVirtual()
        );

        $this->assertIsArray( $list = $this->dummy->listAll() );

        $this->assertContainsOnly(
            \stdClass::class,
            $list
        );

        $this->assertTrue( $this->dummy->eraze() );

        $this->assertFalse(
            $this->dummy->fill(
                $keats = Std::__new()
                    ->$fname('Keats')
                    ->$frole( $poet = 'poet' )
            )->read()
        );

        $this->assertInstanceOf(
            Std::class,
            $this->dummy
                ->init()
                ->fill(
                    Std::__new()
                        ->$fname( $william = 'Shakespeare' )
                        ->$frole($poet)
                )
                ->save()
        );

        $this->assertInstanceOf(
            Std::class,
            $this->dummy
                ->init()
                ->fill($keats)
                ->save()
        );

        $this->assertInstanceOf(
            Std::class,
            $this->dummy
                ->init()
                ->fill( $kant->$fid(0) )
                ->save()
        );

        $this->assertFalse(
            $this->dummy
                ->init()
                ->fill(
                    $roled = Std::__new()->$frole($poet)
                )
                ->read()
        );

        $this->assertIsArray(
            $results = $this->dummy
                ->init()
                ->fill($roled)
                ->readAll()
        );

        $this->assertCount(2, $results);

        $this->assertIsArray(
            $multi = $this->dummy
                ->init()
                ->fill(
                    Std::__new()->$frole( [$role, $poet] ),
                    $this->dummy->type_in
                )
                ->search()
        );

        $this->assertCount(3, $multi);

        $this->assertIsArray(
            $nameOrRole = $this->dummy
                ->init()
                ->multiFill( Std::__new()->$frole($role), $loose = $this->dummy->type_likeLoose )
                ->multiFill( Std::__new()->$fname($william), $loose )
                ->search()
        );

        $this->assertCount(2, $nameOrRole);

        $this->assertFalse(
            $this->dummy
                ->init()
                ->fill(
                    Std::__new()
                        ->$fname(
                            sprintf(
                                '%%\'; %1$s %2$s; --\'',
                                $drop = 'DROP TABLE',
                                $this->dummy->tableName
                            )
                        )
                )
                ->read()
        );

        $this->assertStringNotContainsString( $drop, Database::Instance()->lastSql );

        $this->assertNotEmpty( $this->dummy->listAll() );

        $this->assertCount(
            1,
            $this->dummy
                ->init()
                ->lot(1)
                ->setRequestParameters(1)
                ->listAll()
        );

        $this->assertTrue(
            $this->dummy
                ->init()
                ->fill(
                    Std::__new()->$frole($poet)
                )
                ->deleteAll()
        );
    }
}
