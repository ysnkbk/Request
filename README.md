## Request
============
[![Build Status](https://travis-ci.org/cengizhancaliskan/Request.svg)](https://travis-ci.org/cengizhancaliskan/Request)
[![Latest Stable Version](https://poser.pugx.org/machine/request/v/stable.svg)](https://packagist.org/packages/machine/request)
[![License](https://poser.pugx.org/machine/request/license.svg)](https://packagist.org/packages/machine/request)

This library provides a fast implementation of a requests, responses and cookies.

Installation
------------
Install via composer

```sh
composer require machine/request dev-master
```

Usage
-----
Basic usage example

~~~PHP
<?php

require_once __DIR__ . '/vendor/autoload.php';

$request = new Machine\Http\Request();

or

use Machine\Http\Request;

$request = new Request();

// Get Headers
$headers = $request->getHeaders();
echo '<pre>';
print_r($headers);

// Get All Post Parameters
$params = $request->request;
// get post param
$param  = $request->getPost('name','defaultValue is Optional');

~~~

Testing
-----
You can run the unit tests with the following command:

    $ cd path/to/Machine/Http/
    $ phpunit
