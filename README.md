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
