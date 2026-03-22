<?php

use Utilitools\Includer;

Includer::Instance()
    ->namespaced('Utilitools')
    ->lazy()
    ->setRoot( sprintf( '%1$s/src', __DIR__ ) )
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
