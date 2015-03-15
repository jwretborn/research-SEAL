serviceModule.
	factory('_app', ['$location', function($location) {
		
		var url = function() {
			return '../api/';
		};

		return {
			url : url
		};
	}]);