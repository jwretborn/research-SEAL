/*
 * Hospital controller
 */
mainModule.controller('StatisticsController', ['$scope', '_hospitals',
	function($scope, _hospitals) {
		$scope.hospitals = [];

		_hospitals.hospitals(function(data) {
			$scope.hospitals = data;
		});

		$scope.content = 'templates/statistics/default.html';
	
		$scope.route = function(param) {
			switch(param) {
				case 'days':
					$scope.content = 'templates/statistics/days.html';
					break;
				case 'times':
					$scope.content = 'templates/statistics/times.html';
					break;
				case 'assessments':
					$scope.content = 'templates/statistics/assessment.html';
					break;
				case 'variables':
					$scope.content = 'templates/statistics/variables.html';
					break;
				default:
					$scope.content = 'templates/statistics/default.html';
					break;
			}
		}
	}
]);