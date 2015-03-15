/*
 * Start controller
 */
mainModule.controller('StartController', ['$scope', '_readings',
	function($scope, _readings) {
		$scope.startDate;
		$scope.endDate;
		$scope.remove = false;

		$scope.readings;
		$scope.count;
		$scope.num;

		_readings.readings(function(data) {
			$scope.count = data.length;
			$scope.readings = data;
		});

		$scope.generate = function() {
			var resource = new _readings();
			_readings.generate(resource, $scope.num, $scope.startDate, $scope.endDate, 
				function() {
					_readings.readings(function(data) {
						$scope.startDate = $scope.endDate = $scope.num = undefined;
						$scope.remove = false;
						$scope.count = data.length;
						$scope.readings = data;
					});
				}
			);
		}

		$scope.clear = function() {
			_readings.clearAll(function() {
				_readings.readings(function(data) {
					$scope.remove = false;
					$scope.count = data.length;
					$scope.readings = data;
				});
			});
		}
	}
]);
