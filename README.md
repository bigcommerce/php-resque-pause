php-resque-pause: PHP Resque Pause [![Build Status](https://secure.travis-ci.org/wedy/php-resque-pause.png)](http://travis-ci.org/wedy/php-resque-pause)
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
TBH Im not a PHP guy, I only know some bit about PHP,
so if you reckon there is a better way to do it, please let me know, so far I only know Composer

So I am using Composer, to install this to your project

1. Add php-resque-pause to your project's composer.json

```json
{
    // ...
    "require": {
        "wedy/php-resque-pause": "master"
    },
    // ...
}
```

2. Run `composer install`.

3. If still this is not working for you, too bad it works for me, like i said i`m not really PHP guy, so please google it.. or ask around.

## Pause Jobs ##

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
Wow! thank you, we appreciate any help you can give..

to contribute to this repo to write some code! (or to fix some bugs):

1. Fork this repo
2. Create a branch ```git checkout -b my_branch```
3. Push to your branch ```git push origin my_branch```
4. Create a Pull Request from your branch
5. That's it!

Im not sure what is the PHP coding standard, but please keep it simple, probably 15 lines per method (unless you have really good reason(s)), and please avoid use of nested conditionals for flow of control.

And to be honest im not really fussed about 'commenting' on ever classes, the class name and args names should be sensible, thats good enough for me, you just need to 'comment' on something that needs logical explanation or TODO
```php
public function findMemberById($id) {
}
```
