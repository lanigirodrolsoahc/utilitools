<?php

use Utilitools\Includer;

Includer::Instance()
    ->lazy()
    ->setRoot( sprintf( '%1$s/src', dirname(__FILE__) ) )
        ->to('trait')
        ->inc()
    ->up()
        ->to('system')
        ->inc()
    ->up()
        ->to('t00ls')
        ->files(
            'Std.class.php',
            'Crap.class.php',
            'Debug.class.php',
            'Krypto.class.php',
            'Mailer.class.php',
            'MonthlyMarkDown.class.php',
            'WorkingDays.class.php'
        )
        ->inc()
    ->up()
        ->to('view')
        ->inc();
