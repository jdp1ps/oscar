Monolog Stdout Handler
======================
[![Latest Stable Version](https://poser.pugx.org/kmelia/monolog-stdout-handler/v/stable.png)](https://packagist.org/packages/kmelia/monolog-stdout-handler)
[![Build Status](https://magnum-ci.com/status/1f2c6d566c03de9fff731a3c22b3e98b.png)](https://magnum-ci.com/public/57ca1fcda707236ceb98/builds)

Provides a handler for [Monolog][1] that sends colored messages to stdout.
Messages may be uncolored with a provided formatter.

Loggers are able to interprete a balise language (like bbcode)
Balises currently recognized are :

 * `[c=<color>]...[/c]` with color: `black`, `blue`, `green`, `cyan`, `red`, `purple`, `yellow`, `white`

Example
-------
use stdout handler
```php
<?php
use Monolog\Logger;
use Monolog\Handler\StdoutHandler;

$stdoutHandler = new StdoutHandler();
$logger = new Logger('cronjob');
$logger->pushHandler($stdoutHandler);

$logger->error('[c=green]Hello world![/c]');
```

remove colored formatter
```php
<?php
use Monolog\Formatter\NoColorLineFormatter;
  
$stdoutHandler->setFormatter(new NoColorLineFormatter(StdoutHandler::FORMAT));
```


  [1]: https://github.com/Seldaek/monolog
