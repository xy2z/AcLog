# AcLog

AcLog is a PHP package to log actions to files. This is not for logging errors/warnings/fatal errors, you should probably use Monolog for that. This is meant for history logging actions users make - simple and ready to go.

You should consider to zip (7zip is best) the log files after a few days - it will save ALOT of diskspace.

## Requirements
- PHP 8.0+


## Install
```
composer require xy2z/aclog
```


## Basic Usage
```php
use xy2z\AcLog\AcLog;

$acl = new AcLog(__DIR__ . '/logs');
$acl->log($var); // can be any type: object, array, string, int, etc.
```

### Set Options
```php
$acl = new AcLog(
    log_dir: __DIR__ . '/logs',
    log_date_format: false,
    include_trace: false,
    output_method: AcLog::VAR_DUMP,
    line_breaks_between_header: 4,
    // etc.
);
```

For more options see the constructor method of the AcLog.php file.


## Analyse code
```
vendor\bin\phpstan analyse -c phpstan.neon
```


## Todo
- phpunit
- examples dir
- Get property options