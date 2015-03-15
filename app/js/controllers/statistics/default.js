/*
 * Hospital controller
 */
mainModule.controller('defaultStatisticsController', ['$scope', '_hospitals',
	function($scope, _hospitals) {
		$scope.hospitals = [];
		$scope.forms = {}

		_hospitals.readings(function(data) {

			// For each hospital
			for(i in data) {
				data[i].filled_forms = 0;		// Filled forms
				data[i].filled_readings = 0;	// Filled readings (both doc + nurse)
				data[i].unfilled_forms = 0; 	// Unfilled forms
				data[i].unfilled_readings = 0;	// Unfilled readings (no doc or nurse)
				data[i].part_readings = 0;		// Partially filled form (doc or nurse)
				
				// For each form in the hospital
				for (j in data[i]['forms']) {

					if ( ! _.isNull(data[i]['forms'][j]['question_1'])) {
						// Quetion one filled (incomplete form)
						if (_.isUndefined($scope.forms[data[i]['forms'][j]['reading_id']])) {
							// No form checked for this reading yet
							$scope.forms[data[i]['forms'][j]['reading_id']] = 1;
							data[i].part_readings += 1;
						}
						else {
							// One form cleared for this reading already = filled reading
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
						// Incomplete form
						if (_.isUndefined($scope.forms[data[i]['forms'][j]['reading_id']])) {
							// No form checked for this reading yet, only this incomplete
							$scope.forms[data[i]['forms'][j]['reading_id']] = 0;
						}
						else {
							// We have a check form for this reading
							if ($scope.forms[data[i]['forms'][j]['reading_id']] == 0) {
								// Previous form was incomplete as well, two incomplete forms makes an unfilled reading
								data[i].unfilled_readings += 1;
							}
						}
						// More Unfilled forms
						data[i].unfilled_forms += 1;
					}
				}
			}

			$scope.hospitals = data
		});
		
		$scope.round = function(i) {
			return parseInt(i*100);
		};
	}
]);