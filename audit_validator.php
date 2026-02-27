<?php
  require 'vendor/autoload.php';

  use Opis\JsonSchema\{
    Validator,
    ValidationResult,
    Errors\ErrorFormatter,
  };
  
  if(isset($_FILES["file"])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["file"]["name"]);
    $fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    if($fileType === 'json') {
      move_uploaded_file($_FILES["file"]["tmp_name"], $target_file);
      $validator = new Validator();
    
      $validator->setMaxErrors(15);

      $audit = json_decode(file_get_contents($target_file));

      $result = $validator->validate($audit, json_decode(file_get_contents('JSON_Schema.json')));
      echo '{' . PHP_EOL . '"name": "' . $audit->inventory->name . '",' . PHP_EOL;

      if ($result->isValid()) {
        echo '"valid": true,' . PHP_EOL;
        getFigures($audit);
      } else {
        print_r((new ErrorFormatter())->format($result->error()));
      }
      @unlink($target_file);
    } else {
      echo '{"message": "File is not a JSON."}';
    }
  } else {
    echo '{"message": "No valid file sent."}';
  }


  function getFigures($file) {
    $auditType = 'raweb1';
    if ($file->control_type->name == "Simplifié") { $auditType = 'simple1'; }
    if ($file->control_type->name == "Mobile") { $auditType = 'raam1'; }
    if ($file->control_type->name == "Complet" and $file->audit_reference->name == "RGAA") { $auditType = 'rgaa412'; }

    $levels = [];

    $levFile = json_decode(file_get_contents('criteria/' . $auditType . '/levels.json'));

    foreach ($levFile as $key => $item) {
      $levels[$key]['note'] = "NR";
      $levels[$key]['level'] = $item;
    }

    $audit = $file->pages;
    foreach ($audit as $page) {
      $assessment = $page->assessments;
      foreach ($assessment as $item) {
        $levels[$item->criterion->number]["page" . $page->number] = $item->status->name;
      }
    }

    foreach ($levels as $key => $item) {
      foreach ($item as $elt) {
        if ($elt == "C") {
          if ($levels[$key]['note'] == 'NR' or $levels[$key]['note'] == 'NA') {
            $levels[$key]['note'] = 'C';
          }
        }
        if ($elt == "NA") {
          if ($levels[$key]['note'] == 'NR' or $levels[$key]['note'] == 'NT') {
            $levels[$key]['note'] = 'NA';
          }
        }
        if ($elt == "NC") {
          $levels[$key]['note'] = 'NC';
        }
        if ($elt == "NT") {
          if ($levels[$key]['note'] == 'NR') {
            $levels[$key]['note'] = 'NT';
          }
        }
      }
    }
    print_r($levels);
    $critC = countElts($levels, 'note', 'C');
    $critNC = countElts($levels, 'note', 'NC');
    $lev_A = countElts($levels, 'level', 'A');
    $lev_AA = countElts($levels, 'level', 'AA');
    $critC_A = countElts($levels, 'note', 'C', 'A');
    $critC_AA = countElts($levels, 'note', 'C', 'AA');
    $critNC_A = countElts($levels, 'note', 'NC', 'A');
    $critNC_AA = countElts($levels, 'note', 'NC', 'AA');
    $confA = number_format((float)(($critC_A / ($critC_A + $critNC_A)) * 100), 2, '.', '');
    $confAA = number_format((float)(($critC_AA / ($critC_AA + $critNC_AA)) * 100), 2, '.', '');
    $confLegal = number_format((float)((($critC_A + $critC_AA) / ($critC_A + $critNC_A + $critC_AA + $critNC_AA)) * 100), 2, '.', '');
    $labelSimple = '';
    if ($auditType == "simple1") { 
      $labelSimple = "très bon"; 
      if (floatval($confLegal) <= 80) { $labelSimple = "bon"; }
      if (floatval($confLegal) <= 60) { $labelSimple = "moyen"; }
      if (floatval($confLegal) <= 40) { $labelSimple = "faible"; }
      if (floatval($confLegal) <= 20) { $labelSimple = "très faible"; }
    }
    if ($auditType == "simple1") {
      echo ('"Niveau d\'accessibilité" : "' . $labelSimple . '",' . PHP_EOL);
    }
    echo ('"Audit type" : ' . $auditType . ',' . PHP_EOL);
    echo ('"C level A" : ' . $critC_A . ',' . PHP_EOL);
    echo ('"C level AA" : '. $critC_AA . ',' . PHP_EOL);
    echo ('"NC level A" : ' . $critNC_A . ',' . PHP_EOL);
    echo ('"NC level AA" : ' . $critNC_AA . ',' . PHP_EOL);
    echo ('"Total C" : ' . $critC . ',' . PHP_EOL);
    echo ('"Total NC" : ' . $critNC . ',' . PHP_EOL);
    echo ('"Total criteria" : ' . count($levels) . ',' . PHP_EOL);
    echo ('"Conformité niveau A" : ' . $confA . ',' . PHP_EOL);
    echo ('"Conformité niveau AA" : ' . $confAA . ',' . PHP_EOL);
    echo ('"Conformité AA légale" : ' . $confLegal . PHP_EOL . '}');
  }


  function countElts($res, $elt, $value, $level = "all") {
    $count = 0;
    foreach ($res as $key => $item) {
        if ($level != "all") {
          if ($item[$elt] == $value && $item['level'] == $level) {
            $count++;
          }
        } else {
          if ($item[$elt] == $value) {
            $count++;
          }
        }
    }
    return $count;
  }
?>