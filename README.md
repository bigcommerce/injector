# bigcommerce/injector 
[![Build Status](https://travis-ci.com/bigcommerce/injector.svg?token=rXMck33q3q2Yxpxghp1G&branch=master)](https://travis-ci.com/bigcommerce/injector) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bigcommerce/injector/badges/quality-score.png?b=master&s=9182fe29e72cb72190270e8d2d7940048e6835e9)](https://scrutinizer-ci.com/g/bigcommerce/injector/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/bigcommerce/injector/badges/coverage.png?b=master&s=6c092242baba856ab9172b116482e21cd85c8d32)](https://scrutinizer-ci.com/g/bigcommerce/injector/?branch=master)

Dependency Injector component built on top of Pimple container.

- Also includes an AutoWiring ServiceProvider. 

## Local development
### PhpStan
To check your code with [phpstan](https://phpstan.org/), run `script/phpstan`.

**Remove errors from baseline:**
While changing the code you might see the following error from the PhpStan
```
Ignored error pattern #.... was not matched in reported errors.
```
This means that the error is [no longer present](https://phpstan.org/user-guide/ignoring-errors#reporting-unused-ignores) in the code, so you can remove it from the baseline file.
To do so, run `./vendor/bin/phpstan --generate-baseline=.phpstan/baseline.neon` and commit the changes.

## License
(The MIT License)
Copyright (C) 2015-2017 BigCommerce Inc.
All rights reserved.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
rights to use, copy, modify, merge, publish, distribute, sublicense,and/or sell copies of the Software, and to permit
persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
