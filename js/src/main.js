var myApp = angular.module("my.app", []);
myApp.service('CsvService', function ($http) {
  var data = {
    fetchedPhpData: {},
    csvEntries : {years:[], entries:[]},
    singleSelect : 2014,
    sumData: {sum: 0, count: 0},
    filteredEntries : [],
    scope: {},
    sumFilteredData: {sum: 0, count: 0}
  };

  return {
    update : function (controller) {
      controller.update;
    },
    getFetchedPhpData : function() {
      return this.fetchedPhpData;
    },
    setFetchedPhpData : function(fetchedPhpData) {
      this.fetchedPhpData = fetchedPhpData;
    },
    getCsvEntries : function () {
      return data.csvEntries;
    },
    getSingleSelect : function() {
      return this.singleSelect;
    },
    setSingleSelect : function(singleSelect) {
      this.singleSelect = singleSelect;
    },
    setCsvEntries : function (_data) {
      data.csvEntries = _data;
    },
    getSumData: function () {
      return data.sumData;
    },
    setSumData: function (_data) {
      data.sumData = _data;
    },
    getFilteredEntries: function () {
      return data.filteredEntries;
    },
    setFilteredEntries: function (_data) {
      data.filteredEntries = _data;
    },
    getSumFilteredData: function () {
      return data.sumFilteredData;
    },
    setSumFilteredData: function (_data) {
      data.sumFilteredData = _data;
    }
  };
});

myApp.controller("CsvController", function($scope, $http, CsvService) {
  var self;
  var csv = {
    scope: $scope,
    init : function () {
      self = this;
      this.fetchPhpData();
    },
    initializeCsvData : function (data) {
      CsvService.setFetchedPhpData(data);
      for (var folder in CsvService.getFetchedPhpData()) {
        CsvService.getCsvEntries().years.push(folder);
      }
      self.fetchCsv();
    },
    fetchPhpData : function () {
      var responsePromise = $http.get("/src/Ajax.php");

      responsePromise.success(function(data, status, headers, config) {
        self.initializeCsvData(data);
      });
      responsePromise.error(function(data, status, headers, config) {
        alert("AJAX failed!");
      });
    },
    fetchCsv : function () {
        var entriesOfYear;
        
        var year = 2014;
        
        if (undefined !== $scope.singleSelect) {
          year = $scope.singleSelect;
        } else {
          CsvService.setSingleSelect(year);
        }
        entriesOfYear = CsvService.getFetchedPhpData()[year];
        
        for (var month in entriesOfYear) {
          for (var entry in entriesOfYear[month]) {
            entriesOfYear[month][entry]['skip'] = 0; // for skipping li element if click
          }
        }
        
        CsvService.getCsvEntries().entries = entriesOfYear;
    },
    skipPrice : function (subEntry) {
      subEntry['skip'] = !subEntry['skip'];  
    },
    update : function () {
      $scope.sumFilteredData = CsvService.getSumFilteredData();
    }
  };

  csv.init();
  CsvService.update = csv.update;
  $scope.csvData = CsvService.getCsvEntries();
  $scope.singleSelect = CsvService.getSingleSelect();
  $scope.skipPrice = csv.skipPrice;
  $scope.fetchCsv = csv.fetchCsv;
//    $scope.sumFilteredData = CsvService.getSumFilteredData();

  return csv;
});

myApp.directive("toggleVisibility", function() {
  return {
    link: function (scope, element, attrs) {
      element.bind("click", function () {
        var nextElement = element.next();
        if (!element.hasClass('pass-entry')) {
          element.addClass('pass-entry');
          nextElement.addClass('pass-entry');
          nextElement.text('#NOT# '+nextElement.text())
        } else {
          element.removeClass('pass-entry');
          nextElement.removeClass('pass-entry');
          nextElement.text(nextElement.text().replace('#NOT# ', ''))
        }
      });
    }
  }
});


myApp.filter('total', function (CsvService) {
  return function (input, property, filter) {
    var total = 0;
    var count = 0;
    for (var set in input) {
      var relevantData = input[set].filter( function (el) {
        var regex = new RegExp(filter, 'i');
        var check = regex.test(el['verwendung']) && !el['skip'];
        return check;
      });
      CsvService.setFilteredEntries(relevantData);
      for (var subset in relevantData) {
        if (typeof relevantData[subset] === 'undefined') {
          return 0;
        } else if (isNaN(relevantData[subset][property])) {
          throw 'filter total can count only numeric values';
        } else {
          total += relevantData[subset][property];
          count++;
        }
      }
    }
    CsvService.setSumData({sum: total, count: count});
    var what = {sum: total, count: count};
    CsvService.setSumFilteredData(what);
    CsvService.update();
    return 0;
  };
});