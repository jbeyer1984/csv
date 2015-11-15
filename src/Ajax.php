<?php

function parse_csv_file($csvfile) {
  $csv = array();
  $rowcount = 0;
  $handle = @fopen($csvfile, "r");
  $linesArr = array();
  if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
      $linesArr[] = mb_convert_encoding(str_replace("\n", "", $buffer), 'UTF-8');
//      $linesArr[] = $buffer;
    }
    if (!feof($handle)) {
      echo "Fehler: unerwarteter fgets() Fehlschlag\n";
    }
    fclose($handle);
  }
  
  $header = explode(';', array_shift($linesArr));
  $header = array(
    0 => 'buchung',
    1 => 'wert',
    2 => 'verwendung',
    3 => 'betrag'
  );
  $header_count = count($header);
//  print_r($header);
  
  foreach ($linesArr as $line) {
    $row = explode(';', $line);
    if ($header_count == count($row)) {
      $entry = array_combine($header, $row);
      $entry['betrag'] = floatval(str_replace(",",".", str_replace(".", "", $entry['betrag'])));
      $csv[] = $entry;
    }
  }
  
  return $csv;
}

/************************* BEGIN *************************/
$req = $_REQUEST;

$currentDir = str_replace("\\","/", __DIR__);
$rootDir = $currentDir.'/..';
$entries = scandir($rootDir.'/public/files');
$directories = array();
foreach ($entries as $entry) {
  if ('..' == $entry || '.' == $entry) {
    continue;
  }
  $directories[] = $entry;
}

$files = array();
foreach ($directories as $entry) {
  $fileEntries = glob($rootDir.'/public/files/'.$entry.'/*.csv');
  $files[] = $fileEntries[0];
}
//print_r($files);

$arrJson = array();
foreach ($files as $key => $entry) {
  $res = parse_csv_file($entry);
  $arrJson[$directories[$key]] = $res;
//  print_r($res);
}

//print_r($arrJson);
$toJson = json_encode($arrJson);

echo $toJson;
//
//echo $toJson;