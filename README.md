# CLI component

CLI component for Comely Apps

## Requirements

* PHP >= 7.1

## Installation

`composer require comely-io/cli`

***

## Specification

* Script files in `bin` directory MUST BE named like snake_case
* Script filenames MUST have `.php` extension
* Script classes MUST BE named in snake_case
* Script classes MUST extend `Comely\CLI\Abstract_CLI_Script` class


## Usage

* Install via composer (`composer require comely-io/cli`)
* Copy contents of `dist` directory into your project
* Make `console` file executable (`chmod +x console`)
* If needed, edit `console` file and set correct path to `__bin.php` on line # 3
* If needed, edit `__bin.php` file and set correct path to composer autoload file