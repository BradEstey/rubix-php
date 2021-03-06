Rubix 
=====

[![Latest Stable Version](http://img.shields.io/packagist/v/estey/rubix.svg)](https://packagist.org/packages/estey/rubix) [![Build Status](https://travis-ci.org/BradEstey/rubix-php.svg?branch=master)](https://travis-ci.org/BradEstey/rubix-php) [![Coverage Status](https://img.shields.io/coveralls/BradEstey/rubix-php.svg)](https://coveralls.io/r/BradEstey/rubix-php?branch=master)

This is a PHP wrapper for the [Rubix API](http://rubix.io) by Yeti Media.

Installation
------------

Install this package through [Composer](https://getcomposer.org) by editing your project's `composer.json` file to require `estey/rubix`.

``` json
{
    "require": {
        "estey/rubix": "0.2.*"
    }
}
``` 

Then, update Composer:

``` bash
composer update
```

To get access to the Rubix API Beta go to: [https://rubix.3scale.net](https://rubix.3scale.net)

Usage
-----

To get started, just instantiate a new `Estey\Rubix\Client` class, including your API Key and try the `listCategories()` method.

``` php

use Estey\Rubix\Client;

$rubix = new Client('YOUR API KEY');

// Returns an array of Estey\Rubix\Models\Category
$categories = $rubix->listCategories();

```

Other methods include `addPattern()`, `listPatterns()` and `deletePatterns()` to upload a new pattern set to be recognized, list your pattern sets and delete individual pattern sets. Use the `file` key for uploading images or `remote_file_url` for using a URL.

``` php

// Returns an instance of Estey\Rubix\Models\Pattern
$pattern = $rubix->addPattern([
    'file' => 'path/to/image.jpg',
    'category_name' => 'matching',
    'label' => 'uid'
]);

// Returns an array of Estey\Rubix\Models\Pattern
$rubix->listPattern();

// Returns true if deleted and otherwise throws 
// `Estey\Rubix\Exceptions\NotFoundException`.
$rubix->deletePattern($pattern->id);

```

To use feature matching, a file or file url is needed, as well as a minimum ratio and a minimum matches amount.

``` php

$rubix->featureMatching([
    'remote_file_url' => 'http://example.com/path/to/scene',
    'mr' => 0.9,
    'mma' => 150
]);

```

OCR
---

The Rubix API also offers image text recognition.

``` php

// OCR on the full image.
$rubix->ocr([
    'file' => 'path/to/image.jpg'
]);

```

File Systems
------------

Since this package is built using [Guzzle](http://guzzlephp.org), you can pass an implementation of the `GuzzleHttp\Post\PostFileInterface` into any method that excepts a file upload. This allows you to pass in files from other file systems like S3 or Dropbox by just implementing a simple interface.

``` php

$rubix->addPattern([
    'file' => new \GuzzleHttp\Post\PostFile('file', 'path/to/image.jpg'),
    'category_name' => 'matching',
    'label' => 'uid'
]);

```

License
-------

The MIT License (MIT). Please see [License File](https://github.com/bradestey/rubix-php/blob/master/LICENSE) for more information.

