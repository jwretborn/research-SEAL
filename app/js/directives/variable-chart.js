directiveModule.directive('variableChart', ['$location', function($location) {
	return {
		link : function(scope, element, attrs) {
			var chart;

			var constructChart = function() {
				// Creating chart
				chart = new Highcharts.Chart({
					chart: {
						renderTo: $(element).get(0),
						zoomType: 'xy',
						height: 300,
						borderColor: "#c3c3c3",
						borderWidth: 1
					},
					credits : {
						enabled : false
					},
					title: {
						text: 'Variabelfördelning'
					},
					subtitle: {
						text: ""
					},
					xAxis: [{
						categories: []
					}],
					yAxis: [{ // Primary yAxis
						labels: {
							formatter: function() {
								return this.value +'';
							},
							style: {
								color: '#81C12B'
							}
						},
						title: {
							text: 'Antal',
							style: {
								color: '#81C12B'
							}
						},
						min: 0,
						max: 2.5
					}],
					legend: {
						layout: 'vertical',
						align: 'bottom',
						x: 0,
						verticalAlign: 'top',
						y: 0.1,
						backgroundColor: '#FFFFFF'
					},
					plotOptions: {
						column: {
							//stacking : 'column'
						}
					}
				});
			};

			constructChart();

			scope.$watch('data', function(d) {
				series = [];
				categories = [];
				max = 0;
				measure = 'nurse_assess';
				parsed = scope.mean(d, measure, attrs['id']);

				for (index in d) {
					if ( ! _.isNull(d[index][measure]) && d[index][measure] !== '0') {
						series.push({'x' : d[index][measure], 'y':parseFloat(d[index][attrs['id']])});
						if (parseFloat(d[index][attrs['id']]) > max) {
							max = parseFloat(d[index][attrs['id']]);
						}
					}
				}

				line = []
				for (var i=1; i<7; i++) {
					line.push({'x' : i, 'y' : (parsed['sum']['k']*i)+parsed['sum']['m']});
				}


				if ( ! _.isEmpty(data1) ) {
					chart.yAxis[0].setExtremes(0, max, false);
					chart.addSeries({
								name: attrs['id'],
								color: 'red',
								type: 'scatter',
								index: 0,
								yaxis: 0,
								data: series
							});
					chart.addSeries({
								name: 'Curve - R =' + parsed['sum']['correlation'],
								color: 'blue',
								type: 'line',
								index: 0,
								yaxis: 0,
								data: line
							});
				}
			});
		}
	}
}])