Contributing
============

First of all, **thank you** for contributing, **you are awesome**!

Here are a few rules to follow in order to ease code reviews, and discussions before
maintainers accept and merge your work.

Coding standards
----------------

You MUST follow the [PSR-1](http://www.php-fig.org/psr/1/) and
[PSR-2](http://www.php-fig.org/psr/2/). If you don't know about any of them, you
should really read the recommendations. Can't wait? Use the [PHP-CS-Fixer
tool](http://cs.sensiolabs.org/):

```
$ vendor/bin/php-cs-fixer fix --config-file=.php_cs
```

__Note:__ Never fix coding standards in some existing code as it makes code review more difficult.

Running the test suite
-----------------------

You MUST run the test suite.

- Setup the project using [Composer](http://getcomposer.org/):
  ```
  $ composer install
  ```

- Run the test suite:
  ```
  $ composer test
  ```

You MUST write (or update) unit tests.

Documentation
-------------

You SHOULD write documentation.

Additional notes
----------------

Before submitting your Pull Request, please [rebase your branch](http://git-scm.com/book/en/Git-Branching-Rebasing).  
When submitting your Pull Request, please choose a title that makes sense, it will be used as message for the merge commit if your change is accepted.

Thank you!
