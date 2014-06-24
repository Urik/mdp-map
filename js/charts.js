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
        series: _.chain(chartData).map(function(data) {
            return {
                name: 'Señal del receptor: ' + data.receiverSignal,
                data: _.chain(data.data).filter(function(callData) {
                    return callData.callerSignal !== "99";
                }).map(function(callData) {
                    return {
                        y: calculateAverage(callData.data),
                        x: callData.callerSignal,
                        dataLabels: {
                            enabled: true,
                            formatter: function() {
                                return callData.data.length;
                            }
                        }
                    };
                }).value()
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
        title: {text: 'Tiempo de descarga [msec]'},
        labels: {
            format: '{value} msec'
        }
    },
    series: [{
      name: 'Prueba de descarga',
      data: _(hoursChartData).map(function(data, hour) {
        return {
          y: calculateAverage(_(data).pluck('downloadTime')),
          x: hour,
          dataLabels: {
            enabled: true,
            formatter: function() {
                return data.length;
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
            average: calculateAverage(data.data),
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