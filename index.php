<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="de">
<head>
  <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
  <title>Document</title>
  <script src="js/lib/jquery.min.js"></script>
  <script src="js/lib/angular.js"></script>
</head>
<body ng-app="myapp">
<?php //include_once('src/Ajax.php') ?>
<input type="text" data-ng-model="nameText" placeholder="name" />
<br>
hey

<div ng-controller="CsvController" >
  <button ng-click="myData.doClick(item, $event)">Send AJAX Request</button>
  <br/>
  Data from server: {{myData.fromServer}}
</div>



<div data-ng-controller="CsvController">
  <div data-ng-repeat="(folder, subEntries) in csvData.entries">
    {{folder}}
    <ul data-ng-repeat="subEntry in subEntries | filter:nameText">
<!--      <li>{{subEntry.buchung}}</li>-->
<!--      <li>{{subEntry.wert}}</li>-->
      <li>{{subEntry.verwendung}}</li>
      <li>{{subEntry.betrag}}</li>
    </ul>
  </div>
  <div class="sum">
    {{ csvData.entries|total:'betrag':nameText|currency }}
    sum: {{ sumFilteredData.sum }}<br>
    count: {{ sumFilteredData.count }}<br>
    average: {{ sumFilteredData.sum/sumFilteredData.count }}
  </div>
</div>

<script>
  var myApp = angular.module("myapp", []);
  myApp.service('CsvService', function ($http) {
    var self;
    var csv = {
      csvEntries : {folders:[], entries:[]},
      sumData: {sum: 0, count: 0},
      filteredEntries : [],
      scope: {},
      sumFilteredData: {sum: 0, count: 0},
      init : function () {
        self = this;
        this.fetchCsv();
      },
      fetchCsv : function () {
        var responsePromise = $http.get("/src/Ajax.php");

        responsePromise.success(function(data, status, headers, config) {
//            console.log('data', data);
          for (var folder in data) {
//            console.log('folder', folder);
            self.csvEntries.folders.push(folder);
          }
          self.csvEntries.entries = data;
//            console.log('self.csvEntries.folders', self.csvEntries.folders);
//          $scope.csv.fromServer = data.title;
        });
        responsePromise.error(function(data, status, headers, config) {
          alert("AJAX failed!");
        });
      }
    };
    return csv;
  });
  
  myApp.controller("CsvController", function($scope, $http, CsvService) {
    var self;
    CsvService.init();
    CsvService.scope = $scope;
    $scope.csvData = CsvService.csvEntries;
    $scope.sumFilteredData = CsvService.sumFilteredData;
  });
  
  myApp.filter('total', function (CsvService) {
    return function (input, property, filter) {
      console.log('filter', filter);
      var total = 0;
      var count = 0;
//      if ('undefined' === typeof filter) {
//        return total;
//      }
      
      console.log('property', property);
      for (var set in input) {
        console.log('input[set]', input[set]);
        var relevantData = input[set].filter( function (el) {
//          console.log('el', el);
          var regex = new RegExp(filter, 'i');
          var check = regex.test(el['verwendung']);
          return check;  
        });
        CsvService.filteredEntries = relevantData;
//        console.log('input[set]', input[set]);
        for (var subset in relevantData) {
//          console.log('subset', subset);
//          console.log('relevantData[subset]', relevantData[subset]);
          if (typeof relevantData[subset] === 'undefined') {
            return 0;
          } else if (isNaN(relevantData[subset][property])) {
            throw 'filter total can count only numeric values';
          } else {
            total += relevantData[subset][property];
            count++;
//            console.log('total', total);
          }  
        }  
      }
      CsvService.sumData = {sum: total, count: count};
      var what = {sum: total, count: count};
      console.log('what', what);
      CsvService.sumFilteredData = what;
      CsvService.scope.sumFilteredData = what;
      return 0;
    };
  });
</script>
</body>
</html>