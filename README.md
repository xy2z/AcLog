# AcLog

AcLog is a simple, zero-dependency PHP package to log activity to files.

This is not meant for logging errors, this is can be used for logging cronjobs, emails, user activity, etc.


## Requirements
- PHP 8.0+


## Install
Add this to your existing project:

```
composer require xy2z/aclog
```


## Basic Usage
```php
use xy2z\AcLog\AcLog;

$aclog = new AcLog(__DIR__ . '/logs');
$aclog->log($var); // can be any type: object, array, string, int, etc.
$aclog->log($var, $foo, $bar, $etc); // as many arguments you want.
```

### Set Options
```php
$aclog = new AcLog(
    log_dir: __DIR__ . '/logs',
    log_date_format: false,
    include_trace: false,
    output_method: AcLog::VAR_DUMP,
    line_breaks_between_header: 4,
    // etc.
);
```

For more options see the constructor method of the [AcLog.php](https://github.com/xy2z/AcLog/blob/master/src/AcLog.php) file.


### Log Callbacks
You can add callbacks that will be appended to each `log()` call, examples for this can be user information, request headers, etc. You can add as many callbacks you want.

```php
$aclog = new AcLog($this->logdir);

$aclog->add_log_append_callback(function () {
    return 'callback-1.';
});

$aclog->add_log_append_callback(array('MyClass', 'myCallbackMethod'));
```


## Tips
- Consider to zip (7zip is best) the log files after a few days - it will save ALOT of diskspace.

---

## Developing

Pull Requests are welcome, just make sure your code is tested, analysed and fixed - see below.

Remember to make tests for both classes: `AcLog` and `AcLogStatic`.

```
# Fix Coding Standards (php-cs-fixer)
vendor/bin/php-cs-fixer fix

# Analyse code (phpstan)
vendor/bin/phpstan analyse -c phpstan.neon

# Test code (phpunit)
vendor/bin/phpunit tests --testdox
```


### Todo
- badges
- Later: Method for getting options values
- examples dir?
