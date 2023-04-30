# bigcommerce/injector 

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
