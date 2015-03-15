directiveModule.directive('asDefaultChart', ['$location', function($location) {
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
						text: 'Skattningsfördelning'
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
							//stacking : 'column'
						}
					}
				});
			};

			constructChart();
			
			scope.$watch('assessments', function(d) {
				chart.addSeries({
							name: 'Läkare',
							color: 'red',
							type: 'column',
							index: 0,
							yaxis: 0,
							data: d['doc']
						});
				chart.addSeries({
							name: 'Sjuksköterska',
							color: 'orange',
							type: 'column',
							index: 0,
							yaxis: 0,
							data: d['ssk']
						});
			}, '===');
		
		}
	}
}])