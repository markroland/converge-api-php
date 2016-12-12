Converge API PHP class
======================

[![Build Status](https://travis-ci.org/markroland/converge-api-php.svg?branch=master)](https://travis-ci.org/markroland/converge-api-php)

A PHP class that acts as wrapper for the Converge API.

Converge, formerly VirtualMerchant, is Elavon's online payment platform.

This API should meet the specifications as released in the Converge Developer Guide, November 2015 Revision.


Official API Documentation
--------------------------

[Converge API documentation Site](https://demo.myvirtualmerchant.com/VirtualMerchantDemo/supportlandingvisitor.do)

[Converge API documentation PDF](https://www.myvirtualmerchant.com/VirtualMerchant/download/developerGuide.pdf)

Compatibility
--------------------------
This class should work with PHP >= 5.5.  For PHPUnit 5.5, PHP >= 5.6 is required.

Installation
------------

This Converge API is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

```json
{
    "require": {
        "markroland/converge-api-php": "^0.4"
    }
}
```

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update


Working with the Class
----------------------

Examples are provided in the "examples" folder.


Running Tests
-------------

Run tests from current working directory.

Example: Run with code coverage

    [converge-api-php]# phpunit --coverage-html ./report tests/testConvergeApi.php


Copyright and License
---------------------

The MIT License (MIT)

Copyright (c) 2014 Mark Roland

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.