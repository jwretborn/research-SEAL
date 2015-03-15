serviceModule.
	factory('_hospitals', ['$resource', '_app', '_localStorage', function($resource, _app, _localStorage) {
		var resource = $resource(_app.url()+'data/hospital/:id?t=:timestamp', {});

		resource.hospitals = function(callback, force) {
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

		resource.readings = function(callback, force) {
			resource.query({id:'readings', timestamp:new Date().getTime()}, 
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