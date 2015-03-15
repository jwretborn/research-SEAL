/*
 * Variable controller
 */
mainModule.controller('variableStatisticsController', ['$scope', '_variables',
	function($scope, _variables) {
		$scope.data = [];
		$scope.categories = [];
		$scope.measures = {
			'doc_assess' : {'name' : 'Doc assess', 'value' : false },
			'nurse_assess' : {'name' : 'Nurse assess', 'value' : false},
			'mean_assess' : {'name' : 'Mean assess', 'value' : false}
		}
		$scope.variables = {
			'prio' : {'name' : 'Prio'},
			'awaiting_md' : {'name' : 'Awaiting MD'},
			'patient_hours' : {'name' : 'Patient hours'},
			'admit_index' : {'name' : 'Admit index'},
			'triage_prio' : {'name' : 'Triage prio'},
			'average_time' : {'name' : 'Average time'},
			'occupancy' : {'name' : 'Occupancy'},
			'unseen' : {'name' : 'Unseen'},
			'high_prio' : {'name' : 'High prio'},
			'longest_stay' : {'name' : 'Longest stay'},
			'volume_average' : {'name' : 'Volume average'},
			'mds' : {'name' : 'MDs'},
			'occupancy_rate' : {'name' : 'Occupancy rate'},
			'nurses' : {'name' : 'Nurses'}
		}

		$scope.show = {}

		excluded_keys = ['id', 'reading_id', 'doc_assess', 'mean_assess', 'created', 'object_id'];
		
		$scope.filter = function() {
			$scope.show = $scope.variables;
			
			_variables.composite(function(data) {
				if ( ! _.isUndefined(data[0]) ) {
					l = [];
					for (k in data[0]) {
						if (excluded_keys.indexOf(k) < 0 && k.charAt(0) !== '$') {
							l.push(k);
						}
					}
					$scope.categories = l;
				}
	
				$scope.data = data;
			});
		}

		$scope.mean = function(data, var1, var2) {
			valid = function(v1, v2) {
				if ( ! _.isUndefined(v1) && ! _.isNull(v1) && ! _.isUndefined(v2) && ! _.isNull(v2)) {
					return true;
				}
				else {
					return false;
				}
			};


			arr1 = {};
			arr2 = {};
			arr1['sum'] = 0.0;
			arr2['sum'] = 0.0;
			arr1['var'] = var1;
			arr2['var'] = var2;
			count = 0;
			for (i in data) {
				if ( valid(data[i][var1], data[i][var2]) !== false ) {
					arr1['sum'] += parseInt(data[i][var1]);
					arr2['sum'] += parseFloat(data[i][var2]);
					count++;
				}
			}

			arr1['mean'] = arr1['sum']/count;
			arr2['mean'] = arr2['sum']/count;

			arr1['prod'] = 0;
			arr2['prod'] = 0;
			arr1['sqrd'] = 0;
			arr2['sqrd'] = 0;

			sum = {'sum':0, 'var':0}

			for (i in data) {
				if ( valid(data[i][var1], data[i][var2]) !== false) {
					arr1['prod'] += (data[i][var1]-arr1['mean']);		
					arr2['prod'] += (data[i][var2]-arr2['mean']);
					sum['sum'] += ((data[i][var1]-arr1['mean'])*(data[i][var2]-arr2['mean']));
					arr1['sqrd'] += Math.pow((data[i][var1]-arr1['mean']), 2);
					arr2['sqrd'] += Math.pow((data[i][var2]-arr2['mean']), 2);
				}
			}

			// Calculate varians and standard deviation
			arr1['varians'] = arr1['sqrd']/(count-1);
			arr2['varians'] = arr2['sqrd']/(count-1);
			arr1['stdd'] = Math.sqrt(arr1['varians']);
			arr2['stdd'] = Math.sqrt(arr2['varians']);
			sum['var'] = sum['sum']/(count-1);

			sum['correlation'] = sum['var']/(arr1['stdd']*arr2['stdd']);

			// Calculate the function of the line
			sum['k'] = sum['correlation']*(arr2['stdd']/arr1['stdd']);
			sum['m'] = arr2['mean'] - sum['k'] * arr1['mean'];
			sum['line'] = {}

			return {'sum': sum, var1 : arr1, var2 : arr2};
		};
		
		$scope.round = function(i) {
			return parseInt(i*100);
		};
	}
]);