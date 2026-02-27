<?php

// composer require opis/json-schema
require 'vendor/autoload.php';

use Opis\JsonSchema\{
  Validator,
  ValidationResult,
  Errors\ErrorFormatter,
};

// configure the path to your audits. It should contains subfolders per year, then in each year 2 subfolders "full" for full audits or "simple" for simplified audits
$path = "../audits"

$years = [2021, 2022, 2023, 2024, 2025];
$types = ["full", "simple"];

foreach ($types as $type) {
  foreach ($years as $year) {
    $dir = new DirectoryIterator($path . '/' . $type . '/' . $year . '/');
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot() && $fileinfo->getFilename() != '.keep') {
            $validator = new Validator();
    
            $validator->setMaxErrors(15);
    
            $audit = json_decode(file_get_contents($path . '/' . $type . '/' . $year . '/' . $fileinfo->getFilename()));
    
            $result = $validator->validate($audit, json_decode(file_get_contents('JSON_Schema.json')));
            echo "<br>" . $year . " - " . $type . " - " . $audit->inventory->name . " : ";
    
            if ($result->isValid()) {
              echo "Valid", PHP_EOL;
            } else {
              print_r((new ErrorFormatter())->format($result->error()));
            }
        }
    }
  }
}
