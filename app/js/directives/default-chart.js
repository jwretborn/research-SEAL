
directiveModule.directive('defaultChart', ['$location', function($location) {
	return {
		link : function(scope, element, attrs) {
			var chart;

			var constructChart = function() {
				// Creating chart
				chart = new Highcharts.Chart({
					chart: {
						renderTo: $(element).children('.default-chart').get(0),
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
						categories: ['Lund', 'Malmö', 'Helsingborg', 'Ystad', 'Kristianstad']
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
						max: 120
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
					},
					exporting: {
            			sourceWidth: 400,
            			sourceHeight: 200,
            			// scale: 2 (default)
            		}
				});
			};

			// Make the chart
			constructChart();
			
			// Wait for data
			scope.$watch('hospitals', function(hospitals) {
				data1 = [];
				data2 = [];
				data3 = [];
				categories = [];
				for (index in hospitals) {
					data1.push({'x' : index, 'y':hospitals[index]['filled_readings']});
					data2.push({'x' : index, 'y':hospitals[index]['part_readings']});
					data3.push({'x' : index, 'y':hospitals[index]['unfilled_readings']});
					categories.push(hospitals[index]['name']);
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

			// Add option to export the chart
			$($(element).children('.export')).on('click', function() {
				//var chart = $(element).children('.default-chart').highcharts();
				chart.exportChart({sourceWidth: 600});
			});
		}
	}
}])