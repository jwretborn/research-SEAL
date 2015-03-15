/*
 * Hospital controller
 */
mainModule.controller('assessmentStatisticsController', ['$scope', '_hospitals',
	function($scope, _hospitals) {
		$scope.hospitals = [];
		$scope.forms = {};
		$scope.assessments = {
			'doc' : [0, 0, 0, 0, 0, 0, 0],
			'ssk' : [0, 0, 0, 0, 0, 0, 0],
		};

		_hospitals.readings(function(data) {
			for (i in data) { // Hospitals
				for (j in data[i]['forms']) {
					if ( ! _.isNull(data[i]['forms'][j]['question_1']) ) {
						// We have a question, add it
						if (data[i]['forms'][j]['type'] === '2') {
							$scope.assessments['ssk'][data[i]['forms'][j]['question_1']] += 1;
						}
						else {
							$scope.assessments['doc'][data[i]['forms'][j]['question_1']] += 1;							
						}
					}
				}
			}
		});
		
		$scope.round = function(i) {
			return parseInt(i*100);
		};
	}
]);