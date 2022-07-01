# Graines en l'air - Symfony 6.1 - PHP 8.1

## Presentation

This is a personnal project. Graine en l'air is a seeds donation plateform for french people living in Lyon. It is a free service. It's purpose is to increase biodiversity by encouraging people to grow a vast diversity of reproductible, melliferous, organic or rare plants.

## Getting started

### Prerequisites

1 - Check composer is installed
2 - Check node & yarn are installed

### Install

1 - Clone this project
2 - Run composer install
3 - Run yarn install
4 - Run yarn encore dev to build assets
5 - Create a file .env.local in root (copy of .env file)
6 - Configure your database in .env.local
7 - Configure your favorite smtp in .env.local

## Testing - GrumPHP

GrumPHP, as pre-commit hook, will run several tools when git commit is run :

    * PHP_CodeSniffer to check PSR12
    * PHPStan focuses on finding errors in your code (without actually running it)
    * PHPmd check if PHP best practices are followed
    * ESLint check covers both code quality and coding style issues for JavaScript.
    * TwigCs check Twig coding standard

If tests fail, the commit is canceled and a warning message is displayed to developper.

You can also run each tool one by one :
    * Run php ./vendor/bin/phpcs to launch PHP CodeSniffer
    * Run php ./vendor/bin/phpstan analyse src --level max to launch PHPStan
    * Run php ./vendor/bin/phpmd src text phpmd.xml to launch PHP Mess Detector
    * Run ./node_modules/.bin/eslint assets/js to launch ESLint JS linter
    * Run ../node_modules/.bin/sass-lint -c sass-linter.yml -v to launch Sass-lint SASS/CSS linter