serviceModule.
	factory('_variables', ['$resource', '_app', '_localStorage', function($resource, _app, _localStorage) {
		var resource = $resource(_app.url()+'data/analyze/?start=1362956400&end=1364767200&t=:timestamp', {});

		// Collection = data/analyze/1?start=1362956400&end=1364767200&t=:timestamp
		// Validation = data/analyze/1?start=1367179960&end=1367871160&t=:timestamp
		resource.composite = function(callback, force) {
			resource.query({timestamp:new Date().getTime()}, 
				function(data) { // Success
					/*var dataObj = {};
					for (var res in data) {
						dataObj[data[res].id] = undefined;
					}
					_localStorage.setObject('invoiceList', dataObj);*/
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