<?php

class FileParser {

  /**
   * @var array
   */
  private $data;

  public function __construct()
  {
    $this->data = array();
  }


  public function init()
  {
    $currentDir = str_replace("\\","/", __DIR__);
    $rootDir = $currentDir.'/..';
    $this->readDirAndNest($rootDir . '/public/files');
    
    $this->fetchDataFromCsv();

    $toJson = $this->getFetchedJsonData();
    $dump = print_r($toJson, true);
    error_log("\n" . '-$- in ' . __FILE__ . ':' . __LINE__ . ' in ' . __METHOD__ . "\n" . '*** $toJson ***' . "\n = " . $dump);

    echo $toJson;
  }

  private function getFetchedJsonData()
  {
    return json_encode($this->data);
  }
  
  public function fetchDataFromCsv()
  {
    foreach ($this->data as $year => $yearArr) {
      foreach ($yearArr as $month => $monthArr) {
        foreach ($monthArr as $key => $file) {
          if (false !== strpos($file, '.csv')) {
            $this->data[$year][$month] = $this->parse_csv_file($file);
          }
        }
      }
    }
  }

  private function readDirAndNest($rootDir, $path = '')
  {
    $entries = scandir($rootDir . $path);
    
    foreach ($entries as $entry) {
      if ('..' == $entry || '.' == $entry) {
        continue;
      }
      
      $furtherPath = $path . '/' . $entry;
      if (is_dir($rootDir . $furtherPath)) { // is dir
        $this->readDirAndNest($rootDir, $furtherPath);
      } elseif (false === strpos($entry, '.csv')) { // is file
        continue;
      } else { // is file
        $explodedPath = explode('/', $path);
        $accessedArray = &$this->data;
        foreach($explodedPath as $key) {
          if (empty($key)) {
            continue;
          }
          
          if (is_array($accessedArray) && isset($accessedArray[$key])) {
            $accessedArray = &$accessedArray[$key];  
          } elseif (is_array($accessedArray)) {
            $accessedArray[$key] = array();
            $accessedArray = &$accessedArray[$key];
          } else {
            $accessedArray = array();
          }
        }
        $fileEntries = glob($rootDir . $path . '/*.csv');
        $fileWithPath = $fileEntries[0];
        $accessedArray[] = $fileWithPath;
      }
    }
  }
  
  private function parse_csv_file($csvFile) {
    $csv = array();
    $handle = @fopen($csvFile, "r");
    $linesArr = array();
    if ($handle) {
      while (($buffer = fgets($handle, 4096)) !== false) {
        $linesArr[] = mb_convert_encoding(str_replace("\n", "", $buffer), 'UTF-8');

      }
      if (!feof($handle)) {
        echo "Fehler: unerwarteter fgets() Fehlschlag\n";
      }
      fclose($handle);
    }

    $header = array(
      0 => 'buchung',
      1 => 'wert',
      2 => 'verwendung',
      3 => 'betrag'
    );
    $header_count = count($header);


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
}


/************************* BEGIN *************************/

$fileParser = new FileParser();
$fileParser->init();
