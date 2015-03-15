/*
 * Hospital controller
 */
mainModule.controller('HospitalController', ['$scope', '_hospitals',
	function($scope, _hospitals) {
		$scope.hospitals = [];

		_hospitals.hospitals(function(data) {
			$scope.hospitals = data;
		});
	}
]);