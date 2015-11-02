PHP Resque Pause [![Build Status](https://travis-ci.org/bigcommerce/php-resque-pause.svg?branch=master)](https://travis-ci.org/bigcommerce/php-resque-pause)
==================================

A [PHP-Resque](http://github.com/chrisboulton/php-resque) plugin.

resque-pause adds functionality to pause resque jobs

Using a pause allows you to stop the worker without stop the enqueue

For further information re: php-resque, visit this official repo: <http://github.com/chrisboulton/php-resque>

## Requirements ##
* PHP 5.3+
* Redis 2.2+
* Composer

## Getting Started ##
resque-pause is installed via composer. To install:

```bash
$ # Add php-resque-pause to your project's composer.json
$ composer require "bigcommerce/php-resque-pause"
$ # Install composer dependencies
$ composer install
```

## Usage ##

To use Resque Pause in your application you'll need to create a globally used instance, we use Pimple but you can use
globals, a static variable, or whatever else you like. Upon instantiation `Pause` will add a Resque listener to make
sure that any jobs pushed to a paused queue will be paused as well. On destruction `Pause` will remove said listener.

```php
// Let's put it in a global since that's easy/familiar
$GLOBALS['ResquePause'] = new \Resque\Plugins\Pause(); // Your enqueues are now being listened to
```

### Pause it! ###
```php
$GLOBALS['ResquePause']->pause('My_Queue');
```

### Resume it! ###
```php
$GLOBALS['ResquePause']->resume('My_Queue');
```

### Is it Paused? ###
```php
$GLOBALS['ResquePause']->isPaused('My_Queue');
```

## Contributing ##

This repo is fairly thoroughly tested so please add tests for any feature you add. We use PSR-4 conventions and have a
linter in place. To run the linter simply run `composer lint` and to run the tests locally run `composer test`. To have
your code reviewed please tag @bigcommerce-labs/tools.
