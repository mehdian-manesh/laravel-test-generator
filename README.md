# Laravel Test Generator

Auto generate the unit test file for the available routes

This package require php >= 8.1 and tested by laravel 10.

## Installation

```sh
composer require Mahdianmanesh/laravel-test-generator --dev
```

## Usage

Generating the test file is easy, simply run `php artisan laravel-test:generate` in your project root. This will write all the test cases into the file based on controller.

If you wish to filter for specific routes only, you can pass a filter attribute using --filter, for example `php artisan laravel-test:generate --filter='/api'`

If you wish to change the directory of creating the test file, you can pass a directory using --dir, for example `php artisan laravel-test:generate --dir='V1'`

If you wish to add the @depends attribute to all the function except the first function for running test cases synchronously, you can pass a sync attribute using --sync, for example `php artisan laravel-test:generate --sync='true'`
