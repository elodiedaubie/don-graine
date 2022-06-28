# Graines en l'air - Symfony 6.1 - PHP 8.1

## Presentation

This is a personnal project. Graine en l'air is a seeds donation plateform for french people living in Lyon. It is a free service. It's purpose is to increase biodiversity by encouraging people to grow a vast diversity of reproductible, melliferous, organic or rare plants.

## Tools

### GrumPHP

GrumPHP, as pre-commit hook, will run 3 tools when git commit is run :

PHP_CodeSniffer to check PSR12
PHPStan focuses on finding errors in your code (without actually running it)
PHPmd check if PHP best practices are followed
ESLint check covers both code quality and coding style issues for JavaScript.
TwigCs check Twig coding standard

If tests fail, the commit is canceled and a warning message is displayed to developper.