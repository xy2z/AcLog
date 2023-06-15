# AcLog

AcLog is a simple, zero-dependency PHP package to log activity to files.

AcLog can be used to easily log actions such as cronjobs, emails, user activity, details about errors, etc.

_🧡 Sponsored by **[Datsi.app - Your personal database](https://datsi.app/)**._


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


### Static Class
It is also possible to use it as a static class, if you prefer.
```php
use xy2z\AcLog\AcLogStatic;

AcLogStatic::setup(__DIR__ . '/logs');
AcLogStatic::log($var);
```

If you want to set options in the static class, you need to set them as an array.
```php
AcLogStatic::setup([
    'log_dir' => __DIR__ . '/logs',
    'log_date_format' => false,
    'include_trace' => false,
    'output_method' => AcLog::VAR_DUMP,
    'line_breaks_between_header' => 4,
    // etc.
]);
```

Other than that, it should behave exactly the same as the AcLog class, and all public methods and properties are also available.


#### Static Alias
If you want a shorter name for the static class, you can alias it.
```php
use xy2z\AcLog\AcLogStatic as acl;

acl::setup(__DIR__ . '/logs');
acl::log($var);
```


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
composer fix

# Analyse code (phpstan)
composer analyse

# Test code (phpunit)
composer test
```


### Todo
- badges
- Later: Method for getting options values
- examples dir?
