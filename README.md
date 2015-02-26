php-resque-pause: PHP Resque Pause [![Build Status](https://secure.travis-ci.org/bigcommerce/php-resque-pause.png?branch=master)](https://travis-ci.org/bigcommerce/php-resque-pause)
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

php-resque has a basic event system that can be used by your application
to customize how some of the php-resque internals behave.

php-resque-pause uses beforeEnqueue hook, which called before a job is placed on queue.

### before Enqueue - Pause Callback ###

add below before `Resque::enqueue` method.
```php
//add this on top of your resque:enqueue
ResquePause::beforeEnqueuePauseCallback();

Resque::enqueue('My_Queue', 'My_Job');
```
### Pause it! ###
```php
ResquePause::pause('My_Queue');
```

### Unpause it! ###
```php
ResquePause::unpause('My_Queue');
```

### Is it Paused? ###
```php
ResquePause::isPaused('My_Queue');
```

## Tests ##
we use phpunit for testing. you'll find a bunch of test in ```test```.

Again if you are using composer, you can simply run ```vendor/bin/phpunit```.
Please make sure they pass when you submit a pull request.

Please include tests with your Pull Request.

## Contributing ##

1. Fork this repo
2. Create a branch ```git checkout -b my_branch```
3. Push to your branch ```git push origin my_branch```
4. Create a Pull Request from your branch
5. That's it!

This project will be PSR-4 compliant. So please verify that all pull-requests are such.
