/*
	Wrapper for the HTML5 'localStorage' property.
*/

serviceModule.
	factory('_localStorage', [function() {
		var supportsLocalStorage = false;
		try {
			supportsLocalStorage = 'localStorage' in window && window.localStorage !== null;
		} catch (e) {

		}

		var stringifyObject = function(obj) {
			return window.JSON.stringify(obj);
		};

		var parseStringToJSON = function(string) {
			return $.parseJSON(string);
		};


		// Helper function for setting objects.
		var setObject = function(key, obj) {
			this.setItem(key, this.stringifyObject(obj));
		};

		var setItem = function(key, item) {
			if ( ! supportsLocalStorage ) {
				return null;
			}

			window.localStorage.setItem(key, item);
		};


		// Helper function for fetching objects.
		var getJSONObject = function(key) {
			return this.parseStringToJSON(this.getItem(key));
		};

		var getItem = function(key) {
			if (!supportsLocalStorage) {
				return null;
			}

			return window.localStorage.getItem(key);
		};

		var clear = function() {
			if (!supportsLocalStorage) {
				return null;
			}

			window.localStorage.clear();
		};

		var removeItem = function(key) {
			if (!supportsLocalStorage) {
				return null;
			}

			window.localStorage.removeItem(key);
		};

		return {
			setItem : setItem,
			getItem : getItem,
			removeItem : removeItem,
			clear : clear,
			stringifyObject : stringifyObject,
			parseStringToJSON : parseStringToJSON,
			getJSONObject : getJSONObject,
			setObject : setObject
		};
	}]);