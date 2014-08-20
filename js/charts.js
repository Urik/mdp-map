function createConnectionTimePerSignalChart(element, chartData) {
    $(element).highcharts({
        chart: {
          type: 'spline',
          zoomType: 'xy'
        },
        title: {text: 'Tiempo de conexion promedio'},
        xAxis: {
            title: { text: 'Señal del llamante'},
            categories: _.range(1, 32),
            max: 31
            
        },
        yAxis: {
            title: {text: 'Tiempo de conexion'},
            labels: {
                format: '{value} sec'
            }
        },
        series: _.chain(chartData).map(function(data, receiverSignal) {
            return {
                name: 'Señal del receptor: ' + receiverSignal,
                visible: false,
                data: _(data).map(function(callData, callerSignal) {
                    return {
                        y: parseFloat(callData[0].ConnectionTime),
                        x: callData[0].CallerSignal,
                        dataLabels: {
                            enabled: true,
                            formatter: function() {
                                return callData[0].DataCount;
                            }
                        }
                    };
                })
            };
        }).sortBy('name').value()
    });
}

function createDownloadTimesPerHourChart(element, hoursChartData) {
  $(element).highcharts({
    chart: {
      type: 'spline',
      zoomType: 'xy'
    },
    title: {text: 'Prueba de internet'},
    xAxis: {
      title: { text: 'Horario de prueba'},
      categories: _.range(0, 24),
      max: 24
    },
    yAxis: {
        title: {text: 'Tiempo de descarga en msec'},
        labels: {
            format: '{value} msec'
        }
    },
    series: [{
      name: 'Prueba de descarga',
      data: _(hoursChartData).map(function(data) {
        return {
          y: parseFloat(data.DownloadTime),
          x: data.Hour,
          dataLabels: {
            enabled: true,
            formatter: function() {
                return data.DataCount;
            }
          }
        };
      })
    }]
  });
}

function createConnectionTimePerHour(element, chartData) {
    var minTime = _.chain(chartData).map(function(weekDayValue, weekDayKey) {
        return _(weekDayValue).map(function(weekNumValue, weekNumKey) {
            return _(weekNumValue).map(function(auxData) {
                return parseFloat(auxData.ConnectionTime);
            });
        });
    }).flatten().min().value();
    var weekDaysTranslator = {
        sunday: 'Domingo',
        monday: 'Lunes',
        tuesday: 'Martes',
        wednesday: 'Miercoles',
        thursday: 'Jueves',
        friday: 'Viernes',
        saturday: 'Sabado'
    };
    $(element).highcharts({
        title: {text: 'Tiempo de conexion por hora'},
        xAxis: {
            title: {text: 'Hora'},
            categories: _.range(0, 24),
            data: _(chartData).map(function(x) { return x.hour; })
        },
        yAxis: {
            min: minTime,
            title: {text: 'Tiempo de conexion [sec]'}
        },
        series: _(chartData).map(function(weekDayValue, weekDayKey) {
            var hours = Object.keys(weekDayValue);
            hours = _(hours).sortBy(function(x) {
                return parseInt(x);
            });
            return {
                type: 'spline',
                name: weekDaysTranslator[weekDayKey.toLowerCase()],
                showInLegend: true,
                data: _(hours).map(function(hour) {
                    var hourValue = weekDayValue[hour];
                    var hourKey = hour;
                    return {
                        y: parseFloat(hourValue[0].ConnectionTime),
                        x: parseInt(hourKey),
                        dataLabels: {
                            enabled: true,
                            formatter: function() {
                                return hourValue[0].DataCount;
                            }
                        }
                    };
                })
            };
        })
    });
}

function createConnectionTimePerOperatorChart(element, chartData) {
    return createPerOperatorChart(
        element,
        chartData,
        'Tiempo de conexion promedio por operador',
        'Operadora',
        'Tiempo de conexion [sec]',
        function(data) { return data.Company; },
        function(data) { return parseFloat(data.ConnectionTime); },
        function(data) { return data.DataCount; }
    );
}

function createDownloadTimePerOperatorChart(element, chartData) {
    return createPerOperatorChart(
        element,
        chartData,
        'Tiempo de descarga promedio por operador',
        'Operadora',
        'Tiempo de descarga [sec]',
        function(data) { return data.Operator; },
        function(data) { return parseFloat(data.DownloadTime); },
        function(data) { return data.DataCount; }
    );
}

function createPerOperatorChart(element, chartData, chartTitle, xAxisTitle, yAxisTitle, companyNameAccessor, valueAccessor, recordsCountAccessor) {
    var companiesColors = {
        claro: '#D22D27',
        movistar: '#B3CC08',
        personal: '#0095AB'
    };
    $(element).highcharts({
        chart: {
            type: 'column'
        },
        title: {text: chartTitle},
        xAxis: {
            categories: _(chartData).map(function(data) {
                return companyNameAccessor(data);
            }),
            title: {text: xAxisTitle}
        },
        yAxis: {
            min: 0,
            title: {text: yAxisTitle}
        },
        series: [{
            showInLegend: false,
            data: _(chartData).map(function(data) {
                return {
                    y: valueAccessor(data),
                    name: companyNameAccessor(data),
                    color: companiesColors[companyNameAccessor(data).toLowerCase()],
                    dataLabels: {
                        enabled: true,
                        formatter: function() {
                            return recordsCountAccessor(data);
                        }
                    }
                };
            })
        }]
    });
}

function createSignalsPerNeighborhoodChart(element, chartData) {
    $(element).highcharts({
        chart: {
            type: 'column'
        },
        plotOptions: {
            column: { colorByPoint: true }
        },
        title: {text: 'Señal promedio por barrio' },
        xAxis: {
            title: {text: 'Barrios'},
            type: 'category',
            labels: {
                rotation: 90
            }
        },
        yAxis: {
            min: 0,
            title: {text: 'Señal'}
        },
        series: [{
            name: 'Barrios',
            showInLegend: false,
            data: _(chartData).map(function(data) {
                return {
                    y: parseFloat(data.AverageSignal),
                    name: data.Neighborhood,
                    dataLabels: {
                        enabled: true,
                        formatter: function() {
                            return data.DataCount;
                        }
                    }
                }
            })
        }]
    });
}

var title;
var subtitle;
var select_chart;
var type_time;
var time;
var time_year = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
var time_mouth = ['1','2','3','4','5','6','7','8','9','10','11',
                '12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30'];
var time_day = ['1','2','3','4','5','6','7','8','9','10','11',
                '12','13','14','15','16','17','18','19','20','21','22','23','24'];


function loadChart(){
    type_time =  document.getElementById("time_type").value;
    var range_time = [];
    if(type_time == "day")
        range_time = time_day;
    else if (type_time =="mouth")
        range_time = time_mouth;
    else
        range_time = time_year;
    select_chart = document.getElementById("chart_type").value;
    if(select_chart == "call_signal"){
        basicLine(range_time);
    }else if (select_chart == "call_company"){
        dualAxe(range_time);
    }
}



function basicLine (rangeX) {
        $('#chart').highcharts({
            title: {
                text: 'Monthly Average Temperature',
                x: -20 //center
            },
            subtitle: {
                text: 'Source: WorldClimate.com',
                x: -20
            },
            xAxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
            },
            yAxis: {
                title: {
                    text: 'Temperature (°C)'
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                valueSuffix: '°C'
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            series: [{
                name: 'Tokyo',
                data: [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6]
            }, {
                name: 'New York',
                data: [-0.2, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5]
            }, {
                name: 'Berlin',
                data: [-0.9, 0.6, 3.5, 8.4, 13.5, 17.0, 18.6, 17.9, 14.3, 9.0, 3.9, 1.0]
            }, {
                name: 'London',
                data: [3.9, 4.2, 5.7, 8.5, 11.9, 15.2, 17.0, 16.6, 14.2, 10.3, 6.6, 4.8]
            }]
        });
    }
    
    
function dualAxe(){
  $('#chart').highcharts({
    chart: {
        zoomType: 'xy'
    },
    title: {
        text: 'Average Monthly Temperature and Rainfall in Tokyo'
    },
    subtitle: {
        text: 'Source: WorldClimate.com'
    },
    xAxis: [{
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    }],
    yAxis: [{ // Primary yAxis
        labels: {
            format: '{value}°C',
            style: {
                color: Highcharts.getOptions().colors[1]
            }
        },
        title: {
            text: 'Temperature',
            style: {
                color: Highcharts.getOptions().colors[1]
            }
        }
    }, { // Secondary yAxis
        title: {
            text: 'Rainfall',
            style: {
                color: Highcharts.getOptions().colors[0]
            }
        },
        labels: {
            format: '{value} mm',
            style: {
                color: Highcharts.getOptions().colors[0]
            }
        },
        opposite: true
    }],
    tooltip: {
        shared: true
    },
    legend: {
        layout: 'vertical',
        align: 'left',
        x: 120,
        verticalAlign: 'top',
        y: 100,
        floating: true,
        backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
    },
    series: [{
        name: 'Rainfall',
        type: 'column',
        yAxis: 1,
        data: [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4],
        tooltip: {
            valueSuffix: ' mm'
        }

    }, {
        name: 'Temperature',
        type: 'spline',
        data: [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6],
        tooltip: {
            valueSuffix: '°C'
        }
    }]
});
}
    
   
    
$(function (){ 
    $('#date_chart').datetimepicker({
        format : "YYYY-MM-DD",
        language : 'es'
    });
    $("#clear_chart").click(function() {
        $("#inputDate_chart").val("");
        
    });
    $("#reload_chart").click(function() {
        loadChart();
    });
   });

function calculateAverage(array) {
  return array.length ? _(array).reduce(function(memo, num) {
    return Number(num).valueOf() + Number(memo).valueOf();
  }, 0) / array.length : 0;
}