<h1 align="center">PHPUnit Coverage tools</h1>

<p align="center">
<a href="https://github.com/robiningelbrecht/phpunit-coverage-tools/actions/workflows/ci.yml"><img src="https://github.com/robiningelbrecht/phpunit-coverage-tools/actions/workflows/ci.yml/badge.svg" alt="CI"></a>
<a href="https://github.com/robiningelbrecht/phpunit-coverage-tools/blob/master/LICENSE"><img src="https://img.shields.io/github/license/robiningelbrecht/phpunit-coverage-tools?color=428f7e&logo=open%20source%20initiative&logoColor=white" alt="License"></a>
<a href="https://phpstan.org/"><img src="https://img.shields.io/badge/PHPStan-level%209-succes.svg?logo=php&logoColor=white&color=31C652" alt="PHPStan Enabled"></a>
<a href="https://php.net/"><img src="https://img.shields.io/packagist/php-v/robiningelbrecht/phpunit-coverage-tools?color=%23777bb3&logo=php&logoColor=white" alt="PHP"></a>
<a href="https://phpunit.de/"><img src="https://img.shields.io/packagist/dependency-v/robiningelbrecht/phpunit-coverage-tools/phpunit/phpunit.svg?logo=php&logoColor=white" alt="PHPUnit"></a>
<a href="https://github.com/robiningelbrecht/phpunit-coverage-tools"><img src="https://img.shields.io/packagist/v/robiningelbrecht/phpunit-coverage-tools?logo=packagist&logoColor=white" alt="PHPUnit"></a>
</p>

---

This tool allows you to enforce minimum code coverage by using the clover xml report from PHPUnit. 
Based on the given threshold the testsuite will exit ok if the coverage is higher than the threshold 
or exit with code 1 if the coverage is lower than the threshold. 
This can be used in your continuous deployment environment or for example added to a pre-commit hook.

## Installation

```bash
composer require robiningelbrecht/phpunit-coverage-tools --dev
```

## Configuration

Navigate to your `phpunit.xml.dist` file and add following config to set default options
(you can also set these options at run time):

```xml
<extensions>
    <bootstrap class="RobinIngelbrecht\PHPUnitCoverageTools\PhpUnitExtension">
        <parameter name="exitOnLowCoverage" value="true"/>
    </bootstrap>
</extensions>
```
## Usage

Just run your testsuite like you normally would, but add following aguments.

```bash
vendor/bin/phpunit --coverage-clover=clover.xml -d --min-coverage=100
```
