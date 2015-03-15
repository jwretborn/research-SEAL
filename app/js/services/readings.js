serviceModule.
	factory('_readings', ['$resource', '_app', '_localStorage', function($resource, _app, _localStorage) {
		var resource = $resource(_app.url()+'data/reading/:id/:start/:end?t=:timestamp&filter=:filter', {});

		resource.readings = function(callback, filter, force) {
			resource.query({filter:filter, timestamp:new Date().getTime()}, 
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

		resource.clearAll = function(callback) {
			resource.delete({timestamp:new Date().getTime()},
				function(data){
					callback(data);
				},
				function(data){

				}
			);
		};

		resource.generate = function(rce, count, start, end, callback) {
			rce.$save({id:count, start:start, end:end},
				function(data) {
					callback(data);
				},
				function(data) {

				}
			);
		};

		return resource;
	}]);