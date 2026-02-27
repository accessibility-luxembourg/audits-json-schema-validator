# LU Accessibility audits JSON schema and validator

In Luxembourg, all audits managed by the Information and Press service are available in the [Digital Accessibility Observatory](https://observatoire.accessibilite.public.lu/en/home).
For this to work, all accessibility reports produced in the context of the Web Accessibility Directive monitoring must be made available in JSON format.
This project defines the [JSON Schema](./JSON_Schema.json) to be respected, and also a tool to help validating JSON files in this format.

If you use our [template Excel files](https://accessibilite.public.lu/en/tools/kit.html), you can convert them to JSON with our tool named ["audit-excel2json"](https://github.com/accessibility-luxembourg/audit-excel2json). 

## Features

- Web interface to validate one file according to the JSON format
- CLI tool to validate multiple files.

## Examples
You can find some examples of audit files in JSON in the ["examples"](./examples) folder. All these files comply with the JSON schema.

## Installation

You should have PHP installed on your computer with a webserver. PHP should also be available in CLI.
This project uses composer to install the dependencies. If composer is not available on your computer, [please install it](https://getcomposer.org/).
Install the dependencies with the following command.

```bash
composer install
```

Please create also a folder named "uploads" in this project. This will receive the uploaded files via the web interface.

###

## Usage

### Basic Usage

Load the index.html with your web broser from your local webserver. Upload your JSON file and check the results.

### Batch mode

Create a folder structure like the following:
```
- audits
    - year
        - full
        - simple
```

Place your JSON audit files in the right subfolders. Configure the path variable in the file "batch_validator.php", then run the following command:

```
php batch_validator.php
```

This will validate all the files in this directory structure.

## License

MIT License - see LICENSE file for details.

