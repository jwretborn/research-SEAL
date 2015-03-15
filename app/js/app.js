var mainModule = angular.module('seal', ['services', 'directives']);
var serviceModule = angular.module('services', ['ngResource']);
var directiveModule = angular.module('directives', []);

mainModule.config(['$routeProvider', function($routeProvider) {
	$routeProvider.
		when('/start', {templateUrl: 'templates/start.html', controller: 'StartController'}).
		when('/hospitals', {templateUrl: 'templates/hospitals.html', controller: 'HospitalController'}).
		when('/readings', {templateUrl: 'templates/readings.html', controller: 'ReadingController'}).
		when('/statistics', {templateUrl: 'templates/statistics.html', controller: 'StatisticsController'}).
			otherwise({redirectTo:'/start'});
}]);