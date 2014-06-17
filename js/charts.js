function createConnectionTimePerSignalChart(element, chartData) {
	$(element).highcharts({
		chart: {
      type: 'column'
    },
    title: {text: 'Tiempo de conexion promedio'},
		xAxis: {
			title: 'Señal',
			categories: _(chartData).map(function(x) { return x.signal; })
		},
		yAxis: {
			title: 'Tiempo de conexion',
			labels: {
				format: '{value} sec'
			}
		},
		series: [{
			name: 'Tiempo de conexion',
			data: _(chartData).map(function(x) {
				return {
					y: _(x.data).reduce(function(memo, val) {
							return memo + val;
						}, 0) / x.data.length,	//Promedio tiempo de conexion por chartData
					dataLabels: {
						enabled: true,
						formatter: function() {
							return x.data.length;
						}
					}
				};
			})
		}]
	});
}

function createConnectionTimePerHour(element, chartData) {
	var averageData = _(chartData).map(function(data) {
		return {
			hour: data.hour,
			average: _(data.data).reduce(function(memo, val) {
					return memo + val;
				}, 0) / data.data.length,
			dataCount: data.data.length
		};
	});
	$(element).highcharts({
		/*chart: {
			type: 'column'
		},*/
		title: {text: 'Tiempo de conexion por hora'},
		xAxis: {
			name: 'Hora',
			data: _(chartData).map(function(x) { return x.hour; })
		},
		yAxis: {
			min: _(averageData).chain().pluck('average').min().value()
		},
		series: [{
			name: 'Tiempo de conexion',
			type: 'column',
			data: _(averageData).map(function(x) {
				return {
					y: x.average,
					dataLabels: {
						enabled: true,
						formatter: function() {
							return x.dataCount;
						}
					}
				};
			})
		},
		{
			type: 'spline',
			name: 'Tiempo de conexion',
			showInLegend: false,
			data: _(averageData).map(function(x) {
				return {
					y: x.average,
					dataLabels: {
						enabled: true,
						formatter: function() {
							return x.dataCount;
						}
					}
				};
			})
		}]
	});
}