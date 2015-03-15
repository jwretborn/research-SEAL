directiveModule.directive('daysChart', ['$location', function($location) {
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
						text: 'Sjukshusstatistik'
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
						max: 60
					}],
					legend: {
						layout: 'vertical',
						align: 'right',
						x: 0,
						verticalAlign: 'top',
						y: 10,
						backgroundColor: '#FFFFFF'
					},
					plotOptions: {
						column: {
							stacking : 'column'
						}
					}
				});
			};
			constructChart();
			scope.$watch('days', function(d) {
				data1 = [];
				data2 = [];
				data3 = [];
				categories = [];
				for (index in d) {
					data1.push({'x' : index, 'y':d[index]['filled_readings']});
					data2.push({'x' : index, 'y':d[index]['part_readings']});
					data3.push({'x' : index, 'y':d[index]['unfilled_readings']});
					categories.push(d[index]['desc']);
				}

				if ( ! _.isEmpty(data1) ) {
					chart.xAxis[0].setCategories(categories);
					chart.addSeries({
								name: 'Ej ifyllda',
								color: 'red',
								type: 'column',
								index: 0,
								yaxis: 0,
								data: data3
							});
					chart.addSeries({
								name: 'Delvis ifyllda',
								color: 'orange',
								type: 'column',
								index: 0,
								yaxis: 0,
								data: data2
							});
					chart.addSeries({
								name: 'Ifyllda',
								color: '#81C12B',
								type: 'column',
								index: 0,
								yaxis: 0,
								data: data1
							});
				}
			}, '===');
		}
	}
}])