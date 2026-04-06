<?php

namespace Utilitools;

class System
{
    use Instanced;

    public
    const   COMPANY_COLOR           = 'companyColor',
            COMPANY_CSS             = 'companyCss',
            COMPANY_GOOGLE_URL      = 'companyGoogleUrl',
            COMPANY_LOCATION        = 'companyLocation',
            COMPANY_LOGO            = 'companyLogo',
            COMPANY_LOGO_B64_PNG    = 'companyLogoPng',
            COMPANY_NAME            = 'companyName',
            COMPANY_PHONE           = 'companyPhone',
            COMPANY_WEBSITE         = 'companyWebsite',
            COMPONENT_PATH          = 'componentPath',
            LOCALE                  = 'locale',
            ROOT_API                = 'rootApi',
            ROOT_INDEX              = 'rootIndex',
            ROOT_SERVICES           = 'rootServices',
            SIXTY_FOR_ICON          = 'b64Icon',
            SMTP_NAME               = 'smtpName',
            SMTP_PORT               = 'smtpPort',
            SMTP_PWD                = 'smtpPwd',
            SMTP_USER               = 'smtpUser',
            SMTP_SERVER             = 'smtpServer',
            SQL_BASE                = 'sqlDatabase',
            SQL_HOST                = 'sqlHost',
            SQL_PORT                = 'sqlPort',
            SQL_PWD_HOME            = 'sqlPwdHome',
            SQL_PWD_WORK            = 'sqlPwdWork',
            SQL_USER                = 'sqlUser',
            SQLite_FILE             = 'sqlite';

    protected   $b64Icon,
                $companyColor,
                $companyCss,
                $companyGoogleUrl,
                $companyLocation,
                $companyLogo,
                $companyLogoPng,
                $companyName,
                $companyPhone,
                $companyWebsite,
                $componentPath,
                $locale,
                $rootApi,
                $rootIndex,
                $rootServices,
                $smtpName,
                $smtpPort,
                $smtpPwd,
                $smtpUser,
                $smtpServer,
                $sqlite,
                $sqlPwdHome,
                $sqlPwdWork,
                $sqlUser;

    /**
     * Use `SsoSystem::Instance()`
     */
    private
    function __construct () {}

    /**
     * gets a property while to be found
     *
     * @param   string  $property
     *
     * @return  mixed|false
     */
    public
    function __get ( string $property )
    {
        return \property_exists($this, $property) ? $this->$property : false;
    }

    /**
     * registers all parameters
     *
     * @param   Std         $data
     *
     * @return  static
     */
    public
    function register ( Std $data ) : System
    {
        foreach ( $data as $property => $value )
            if ( \property_exists($this, $property) )
                $this->$property = $value;

        return $this;
    }
}
