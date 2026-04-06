# Utilit00ls

Common tools for quick PHP development – lightweight helpers, database utilities, JS helpers, and more.

[![Latest](https://img.shields.io/packagist/v/lanigirodrolsoahc/utilitools.svg)](https://packagist.org/packages/lanigirodrolsoahc/utilitools)
[![Downloads](https://img.shields.io/packagist/dt/lanigirodrolsoahc/utilitools.svg)](https://packagist.org/packages/lanigirodrolsoahc/utilitools)

## PHP Compatibility

[![PHP 7.4.32](https://img.shields.io/badge/php-7.4.32-8892BF)](https://www.php.net/releases/7_4_32.php)
[![PHP 8.2.13](https://img.shields.io/badge/php-8.2.13-4F5B93)](https://www.php.net/releases/8_2_13.php)

## Installation
```bash
composer require lanigirodrolsoahc/utilitools
```

## Structure
```txt
utilit00ls
├── documentation/
│   └── visual.mermaid.md
├── src/
│   ├── interface/
│   │   ├── locale/
|   |   |   └──French.class.php
│   │   └── Locale.interface.php
│   ├── js/
│   │   ├── Helper.class.js
│   │   ├── Loader.class.js
│   │   └── Tooltip.class.js
│   ├── system/
│   │   ├── Database.class.php
│   │   ├── Sql.class.php
│   │   └── System.class.php
│   ├── t00ls/
│   │   ├── Crap.class.php
│   │   ├── Debug.class.php
│   │   ├── Includer.class.php
│   │   ├── Krypto.class.php
│   │   ├── Mailer.class.php
│   │   ├── MonthlyMarkDown.class.php
│   │   ├── Std.class.php
│   │   └── WorkingDays.class.php
│   ├── trait/
│   │   ├── Databased.trait.php
│   │   ├── Dates.trait.php
│   │   ├── Errors.trait.php
│   │   ├── Instanced.trait.php
│   │   └── VirtualObject.trait.php
│   └── view/
|       ├── style/
|       |   └──Periods.style.css
│       |── HtmlGenerator.class.php
│       └── Period.class.php
├── tests/
│   ├── dates.Test.php
│   ├── DummyKrypto.class.php
│   ├── DummyVO.class.php
│   ├── krypto.Test.php
│   ├── markdown.Test.php
│   ├── MockMarkDown.class.php
│   ├── period.Test.php
│   ├── std.Test.php
│   ├── system.Test.php
│   └── vo.Test.php
├── .gitattributes
├── .gitignore
├── autoload.php
├── composer.json
├── LICENSE
├── phpunit.xml
└── README.md
```

## Usage
```php
require dirname(__FILE__).'/vendor/autoload.php';

use Utilitools\Std;

Std::__new();
```

## Test
```bash
php ./vendor/bin/phpunit vendor/lanigirodrolsoahc/utilitools/tests --testdox
```

## License

MIT License

Copyright (c) lanigirodrolsoahc

See the [LICENSE](LICENSE) file for details.
