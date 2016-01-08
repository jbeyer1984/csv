<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="de">
<head>
  <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
  <title>Document</title>
  <script src="js/lib/jquery.min.js"></script>
  <script src="js/lib/angular.js"></script>
  <link rel="stylesheet" href="/css/main.css">
</head>
<body ng-app="my.app">
  <input type="text" data-ng-model="search" placeholder="name" />
  <br>
  
  <div ng-controller="CsvController" >
    <select ng-options="year for (key, year) in csvData.years" name="singleSelect" ng-model="singleSelect" ng-change="fetchCsv()">
      <option value="" ng-if="false"></option>
    </select><br>
  
    <div id="billings">
      <div data-ng-repeat="(folder, subEntries) in csvData.entries">
        {{ folder }}
        <ul data-ng-repeat="subEntry in subEntries | filter:search">
          <li ng-click="skipPrice(subEntry)" toggle-visibility>{{subEntry.verwendung}}</li>
          <li>{{subEntry.betrag}}</li>
        </ul>
      </div>
      <div class="sum">
        {{ csvData.entries|total:'betrag':search|currency }}
        sum: {{ sumFilteredData.sum }}<br>
        count: {{ sumFilteredData.count }}<br>
        average: {{ sumFilteredData.sum/sumFilteredData.count }}
      </div>
    </div>
  </div>
  
  <script src="js/src/main.js"></script>
</body>
</html>