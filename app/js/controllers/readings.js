/*
 * Reading controller
 */
mainModule.controller('ReadingController', ['$scope', '_readings', '_hospitals',
	function($scope, _readings, _hospitals) {
		$scope.readings = [];
		$scope.hospitals = [];
		$scope.selection = -1;

		_hospitals.hospitals(function(data) {
			$scope.hospitals = data;
		});

		_readings.readings(function(data) {
			var m = data.length;
			for (var i=0; i<m; i++) {
				var d = new Date(data[i].timestamp*1000);
				data[i].day = d.getDate()+'/'+(d.getMonth()+1);
				data[i].hour = ((d.getHours() < 10) ? '0'+d.getHours() : d.getHours())+':'+((d.getMinutes() < 10) ? '0'+d.getMinutes() : d.getMinutes());
			}
			$scope.readings = data;
		});

		$scope.filter = function() {
			_readings.readings(function(data) {
				var m = data.length;
				for (var i=0; i<m; i++) {
					var d = new Date(data[i].timestamp*1000);
					data[i].day = d.getDate()+'/'+(d.getMonth()+1);
					data[i].hour = ((d.getHours() < 10) ? '0'+d.getHours() : d.getHours())+':'+((d.getMinutes() < 10) ? '0'+d.getMinutes() : d.getMinutes());
				}
				$scope.readings = data;
			}, $scope.selection);
		}
	}
]);