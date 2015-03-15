/*
 * Times controller
 */
mainModule.controller('daysStatisticsController', ['$scope', '_times',
	function($scope, _times) {
		$scope.days = [];
		$scope.forms = {}

		_times.days(function(data) {
			for(i in data) {
				data[i].filled_forms = 0;
				data[i].filled_readings = 0;
				data[i].unfilled_forms = 0;
				data[i].unfilled_readings = 0;
				data[i].part_readings = 0;
				for (j in data[i]['forms']) {
					if ( ! _.isNull(data[i]['forms'][j]['question_1'])) {
						if (_.isUndefined($scope.forms[data[i]['forms'][j]['reading_id']])) {
							$scope.forms[data[i]['forms'][j]['reading_id']] = 1;
							data[i].part_readings += 1;
						}
						else {
							if ( $scope.forms[data[i]['forms'][j]['reading_id']] > 0 ) {
								data[i].filled_readings += 1;
								data[i].part_readings -= 1;
							}
							else {
								data[i].part_readings += 1;
							}
							$scope.forms[data[i]['forms'][j]['reading_id']] += 1;
						}
						data[i].filled_forms += 1;
					}
					else {
						if (_.isUndefined($scope.forms[data[i]['forms'][j]['reading_id']])) {
							$scope.forms[data[i]['forms'][j]['reading_id']] = 0;
						}
						else {
							if ($scope.forms[data[i]['forms'][j]['reading_id']] == 0) {
								data[i].unfilled_readings += 1;
							}
						}
						data[i].unfilled_forms += 1;
					}
				}
			}

			$scope.days = data
		});
	
		$scope.round = function(i) {
			return parseInt(i*100);
		};
	}
]);