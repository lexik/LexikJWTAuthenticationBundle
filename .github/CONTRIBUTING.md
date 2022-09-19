# Contributing

First of all, **thank you** for contributing.

Bugs or feature requests can be posted online on the GitHub issues section of the project.

Few rules to ease code reviews and merges:

- You MUST follow the [PSR-12](http://www.php-fig.org/psr/psr-12/) coding standards.
- You MUST run the test suite.
- You MUST write (or update) unit tests when bugs are fixed or features are added.
- You SHOULD write documentation.

We use [Git-Flow](http://jeffkreeftmeijer.com/2010/why-arent-you-using-git-flow/) to automate our git branching
workflow.

To contribute use [Pull Requests](https://help.github.com/articles/using-pull-requests), please, write commit messages
that make sense, and rebase your branch before submitting your PR.

May be asked to squash your commits too. This is used to "clean" your Pull Request before merging it, avoiding commits
such as fix tests, fix 2, fix 3, etc.

Run test suite
------------

* install composer: `curl -s http://getcomposer.org/installer | php`
* install dependencies: `php composer.phar install`
* run tests: `vendor/bin/phpunit`
