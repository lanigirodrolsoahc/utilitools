<?php

use PHPUnit\Framework\TestCase;
use Utilitools\Std;
use Utilitools\System;

class T_system extends TestCase
{
    private
    const   API     = 'yourapp/api',
            PLACE   = 'https://www.google.com/maps/place/yourowndata';

    private System $system;

    protected
    function setUp () : void
    {
        ( $this->system = System::Instance() )
            ->register(
                Std::__new()
                ->{ System::ROOT_API }(self::API)
                ->{ System::ROOT_INDEX }('yourapp/')
                ->{ System::ROOT_SERVICES }('yourapp/services')
                ->{ System::SMTP_NAME }('yourapp SMTP')
                ->{ System::SMTP_PORT }(587)
                ->{ System::SMTP_PWD }('app SMTP pwd')
                ->{ System::SMTP_USER }('your.app@yourapp.com')
                ->{ System::SMTP_SERVER }('smtp.yourapp.com')
                ->{ System::SQL_PWD_HOME }('')
                ->{ System::SQL_PWD_WORK }('yourappsqlpwd')
                ->{ System::SQL_USER }('youruser')
                ->{ System::COMPANY_NAME }('your company')
                ->{ System::COMPANY_COLOR }('#f580ca')
                ->{ System::COMPANY_GOOGLE_URL }(self::PLACE)
                ->{ System::COMPANY_LOCATION }('yourlocation')
                ->{ System::COMPANY_PHONE }('+yourphonenumber')
                ->{ System::COMPANY_WEBSITE }('http://yourapp.com')
                ->{ System::COMPANY_LOGO_B64_PNG }('your b64 encoded png logo')
            );
    }

    public
    function test_singleton () : void
    {
        $this->assertSame( $this->system, System::Instance() );
    }

    public
    function test_assignment () : void
    {
        $this->assertEquals( self::API, $this->system->{ System::ROOT_API } );

        $this->assertEquals( self::PLACE, $this->system->{ System::COMPANY_GOOGLE_URL } );
    }

    public
    function test_ignoreUnknown () : void
    {
        $this->system->register(
            Std::__new()->{ $unknown = 'unknown' }(self::PLACE)
        );

        $this->assertFalse( $this->system->$unknown );
    }

    public
    function test_unknown () : void
    {
        $this->assertFalse( $this->system->lambda );
    }
}
