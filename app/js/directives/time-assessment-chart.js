directiveModule.directive('timeAssessmentChart', ['$location', function($location) {
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
						text: 'Tidsstatistik'
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
						}
					}
				});
			};
			constructChart();
			scope.$watch('times', function(d) {
				data = [[], [], [], [], [], [], []];
				categories = [];
				for (i in d) {
					sum = 0;
					count = 0;
					tmp = [0, 0, 0, 0, 0, 0]
					for (j in d[i]['forms']) {
						if ( ! _.isNull(d[i]['forms'][j]['question_1']) ) {
							tmp[d[i]['forms'][j]['question_1']-1] += 1;
							count += 1;
							sum += d[i]['forms'][j]['question_1'];
						}
					}

					for (k in tmp) {
						data[k].push({'x' : i, 'y': tmp[k]});
					}

					categories.push(d[i]['relativehour']+':00');
				}

				if ( ! _.isEmpty(data) ) {
					chart.xAxis[0].setCategories(categories);
					for (m in data) {
						chart.addSeries({
								name: 'Skattning '+(parseInt(m)+1),
								type: 'column',
								index: 0,
								yaxis: 0,
								data: data[m]
							});
					}
				}
			}, '===');
		}
	}
}])