serviceModule.
	factory('_times', ['$resource', '_app', '_localStorage', function($resource, _app, _localStorage) {
		var resource = $resource(_app.url()+'data/timepoint/:id?t=:timestamp', {});

		resource.timepoints = function(callback, force) {
			resource.query({timestamp:new Date().getTime()}, 
				function(data) { // Success
					if(typeof(callback) === 'function') {
						callback(data);
					}	
				},
				function(data) { // Error
					_errorHandler.add(data);
				}
			);
		};

		resource.readings = function(callback, force) {
			resource.query({id:'readings', timestamp:new Date().getTime()}, 
				function(data) { // Success
					if(typeof(callback) === 'function') {
						callback(data);
					}	
				},
				function(data) { // Error
					_errorHandler.add(data);
				}
			);
		};

		resource.days = function(callback, force) {
			resource.query({id:'days', timestamp:new Date().getTime()}, 
				function(data) { // Success
					if(typeof(callback) === 'function') {
						callback(data);
					}	
				},
				function(data) { // Error
					_errorHandler.add(data);
				}
			);
		};

		return resource;
	}]);