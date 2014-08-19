var ENDPOINT = 'http://localhost/mdp-map/index.php/';
var map;
var mapOptions = {
	zoom : 12,
	center : new google.maps.LatLng(-38, -57.55),
	mapTypeId : google.maps.MapTypeId.TERRAIN
};
var markers = [];
var infos = [];
var colored = [];
var zones = [];
//array for map zones polygons

var neighArray = {};
var callArray = [];

function displayNeighborhoods(neighborhoods) {
	var lastNeigh = "";
	var groupedNeighborhoodsByZone = _.groupBy(neighborhoods, 'zone_id');
	for (var zone_id in groupedNeighborhoodsByZone) {
		(function() {
			zones[zone_id] = [];
			var neighs = groupedNeighborhoodsByZone[zone_id];
			var groupedNeighborhoodsByName = _.groupBy(neighs, 'name');
			for (var name in groupedNeighborhoodsByName) {
				(function() {
					var neighborhood = groupedNeighborhoodsByName[name];
					var coordinates = _.map(neighborhood, function(x) {
						return new google.maps.LatLng(x.lat, x.lon);
					});
					var neighborhoodPolygon = new google.maps.Polygon({
						paths : coordinates,
						strokeColor : '#000000',
						strokeOpacity : 0.8,
						strokeWeight : 3,
						//regex for random colors: fillColor : '#' + (0x1000000 + Math.random() * 0xFFFFFF).toString(16).substr(1, 6),
						fillColor : 'darkblue',
						fillOpacity : 0.5
					});
					neighborhoodPolygon.setMap(map);
					neighArray[neighborhood[0].id] = neighborhoodPolygon;
					zones[zone_id].push(neighborhoodPolygon);
				})();
			}
		})();
	}
}

function clearMarkers() {
	for (var i = 0; i < markers.length; i++) {
		markers[i].setMap(null);
	}
	markers = [];
}

function clearInfos() {
	for (var i = 0; i < infos.length; i++) {
		infos[i].setMap(null);
	}
	infos = [];
}

function colorNeighs(color) {
	for (var i = 0; i < colored.length; i++) {
		colored[i].setOptions({
			fillColor : color
		});
		google.maps.event.clearListeners(colored[i],'click');
	}
	colored = [];

}

function loadInternetMarkers(markers) {
	var data = _.map(markers, function(x) {
		return createMarkerWindowData(x.sourceNumber, x.operatorName, x.batteryLevel, x.currentSignal, x.locationLat, x.locationLon, x.dateCreated, {
			'Tiempo de descarga [ms]' : x.downloadTime
		});
	});

	var failedDownloadColor = '#5E5E5E';

	loadMarkers('Test de internet', data, function(markerData, marker) {
		if (markerData.windowContent['Tiempo de descarga [ms]'] == 0) {
			marker.setIcon('http://maps.google.com/mapfiles/marker_black.png');
		}
	});
}

function loadSmsMarkers(smsData) {
	var data = _.map(smsData, function(x) {
		return createMarkerWindowData(x.sourceNumber, x.operatorName, x.batteryLevel, x.currentSignal, x.locationLat, x.locationLon, x.dateCreated, {
			'Tiempo de envio [ms]' : x.sendingTime
		});
	});

	loadMarkers('Envio de SMS', data);
}

function loadCallsMarkers(callsData) {
	var markerData = _.map(callsData, function(x) {
		return createMarkerWindowData(x.caller_number, x.caller_operator_name, x.caller_battery_level, x.caller_signal, x.caller_lat, x.caller_lon, x.caller_time, {
			'Señal del Destinatario' : x.receiver_signal,
			'Tiempo de Conexion' : x.connection_time
		});
	});

	loadMarkers('Llamada', markerData);
}

function loadAVGCallData(AVGCallData) {
	var data = _(AVGCallData).map(function(x) {
		return new CallsZoneData({
			avgConnectionTime : x.avg_connection_time,
			avgSignal : x.avg_signal,
			avgRecSignal : x.avg_rec_signal,
			neighId : x.neighborhood_id,
			neighName : x.name,
			numRegs : x.num_regs
		});
	});

	loadAVGWindows(data);
}

function loadAVGInternetData(AVGInternetData) {
	var data = _(AVGInternetData).map(function(x) {
		return new InternetZoneData({
			avgDownloadTime : x.avg_download_time,
			avgSignal : x.avg_signal,
			neighId : x.neighborhood_id,
			neighName : x.name,
			numRegs : x.num_regs
		});
	});

	loadAVGWindows(data);
}

function loadAVGSmsData(AVGSmsData) {
	var data = _(AVGSmsData).map(function(x) {
		return new SmsZoneData({
			avgSendingTime : x.avg_sending_time,
			avgSignal : x.avg_signal,
			neighId : x.neighborhood_id,
			neighName : x.name,
			numRegs : x.num_regs
		});
	});

	loadAVGWindows(data);
}

function loadAVGSignalData(AVGSignalData) {
	var data = _(AVGSignalData).map(function(x) {
		return new SignalZoneData({
				avgSignal : x.avg_signal,
				neighId : x.neighborhood_id,
				neighName : x.name,
				numRegs: x.num_regs
		});
	});

	loadAVGWindows(data);
}

function loadFailedInternetConnections(AVGFailedData) {
	var data = _(AVGFailedData).map(function(x) {
		return new FailedInternetZoneData({
			avgFailures: x.failed_downloads_percentage,
			avgSignal: x.signal_average,
			numRegs: x.total_samples,
			neighId : x.neighborhood_id,
			neighName : x.neighborhood_name,
		});
	});

	loadAVGWindows(data);
}

function createMarkerWindowData(callerNumber, operatorName, callerBatteryLevel, callerSignal, lat, lon, date, customData) {
	return {
		callerLat : lat,
		callerLon : lon,
		sourceNumber : callerNumber,
		operatorName : operatorName,
		batteryLevel : callerBatteryLevel,
		sourceSignal : callerSignal,
		date : date,
		windowContent : customData
	};
}

//markerCustomFunction should be a function accepting the data variable, and its marker.
function loadMarkers(title, totalData, markerCustomFunction) {
	clearMarkers();
	if (infos.length > 0) {
		colorNeighs('darkblue');
		clearInfos();
	}
	$.each(totalData, function(i, data) {
		(function() {
			var marker = new google.maps.Marker({
				position : new google.maps.LatLng(data.callerLat, data.callerLon),
				title : title,
				opacity: 0.5
			});
			marker.setMap(map);
			markers.push(marker);
			var contentString = '<p>Numero de origen: ' + data.sourceNumber + '</p>';
			contentString += '<p>Fecha de evento: ' + data.date + '</p>';
			contentString += '<p>Operador: ' + data.operatorName + '</p>';
			contentString += '<p>Nivel de bateria: ' + data.batteryLevel + '</p>';
			contentString += '<p>Señal del emisor: ' + data.sourceSignal + '</p>';
			for (var key in data.windowContent) {
				contentString += '<p>' + key + ': ' + data.windowContent[key] + '</p>';
			}

			var infoWindow = new google.maps.InfoWindow({
				content : contentString
			});

			if (markerCustomFunction) {
				markerCustomFunction(data, marker);
			}
			google.maps.event.addListener(marker, 'click', function() {
				infoWindow.open(map, marker);
			});
		})();
	});
}

function polygonCenter(poly) {
	var lowx, highx, lowy, highy, lats = [], lngs = [], vertices = poly.getPath();

	for (var i = 0; i < vertices.length; i++) {
		lngs.push(vertices.getAt(i).lng());
		lats.push(vertices.getAt(i).lat());
	}

	lats.sort();
	lngs.sort();
	lowx = lats[0];
	highx = lats[vertices.length - 1];
	lowy = lngs[0];
	highy = lngs[vertices.length - 1];
	center_x = lowx + ((highx - lowx) / 2);
	center_y = lowy + ((highy - lowy) / 2);
	return (new google.maps.LatLng(center_x, center_y));
}

function loadAVGWindows(totalData) {

	clearMarkers();
	if (infos.length > 0) {
		colorNeighs('darkblue');
		clearInfos();
	}

	$.each(totalData, function(i, data) {
		(function() {

			var infoWindow = new google.maps.InfoWindow();

			var contentString = '<p style="color:red"><b>Barrio ' + data.entity.neighName + '</b></p>';
			var fields = data.getData();
			for (var key in fields) {
				contentString += '<p>' + key + ': ' + fields[key] + '</p>';
			}
			
			if (data.shouldPaintZone()) {
				neighArray[data.entity.neighId].setOptions({
					fillColor : data.getColor()
				});
				colored.push(neighArray[data.entity.neighId]);
			}

			infoWindow.setContent(contentString);
			infoWindow.setPosition(polygonCenter(neighArray[data.entity.neighId]));
			infos.push(infoWindow);

			var showInfo = function(event) {
				infoWindow.setPosition(event.latLng);
				infoWindow.open(map);

			};

			google.maps.event.addListener(neighArray[data.entity.neighId], 'click', showInfo);

		})();
	});

}

function getFields() {
	var first = true;
	var filterString = "";
	if (document.getElementById("inputDateFrom").value != "" && document.getElementById("inputDateTo").value != "") {
		filterString += "?dateFrom=" + document.getElementById("inputDateFrom").value + "&dateTo=" + document.getElementById("inputDateTo").value;
		first = false;
	}
	if ($("#filterNumber").val() != "") {
		if (first)
			filterString += "?"
		else
			filterString += "&"

		filterString += "number=" + $("#filterNumber").val();
	}

	return filterString;
}

function getConnectionTimePerSignalData() {
	$.get('index.php/calls/avgcalltimepersignals', function(data) {

	});
}

function handleReceivedCallsData(data) {
	var parsedData = JSON.parse(data);
	loadCallsMarkers(parsedData);
	var signalsChartData = _.chain(parsedData).groupBy("receiver_signal").map(function(values, receiverSignal) {
		return {
			receiverSignal: receiverSignal,
			data: _.chain(values).groupBy("caller_signal").map(function(values, callerSignal) {
				return {
					callerSignal: callerSignal,
					data: _(values).map(function(val) { return moment.duration(val.connection_time).asSeconds();})
				};
			}).value()
		};
	}).value();

	$.get('index.php/api/calls/avgcalltimepersignals', function(data) {
		createConnectionTimePerSignalChart($('#signalsChart'), JSON.parse(data));
		$('#signalsChart').css({display: 'block'});
		$(window).trigger("resize");
	});

	$.get('index.php/api/calls/avgcalltimeperdayandhour', function(data) {
		createConnectionTimePerHour($('#hoursChart'), JSON.parse(data));
		$('#hoursChart').css({display: 'block'});
		$(window).trigger("resize");
	});

	$.get('index.php/api/calls/avgcalltimeperoperator', function(data) {
		createConnectionTimePerOperator($('#operatorsChart'), JSON.parse(data));
		$('#operatorsChart').css({display: 'block'});
		$(window).trigger("resize");
	});
}

function handleReceivedInternetData(data) {
	var internetData = JSON.parse(data);
	loadInternetMarkers(internetData);
	var hoursChartData = _(internetData).groupBy(function(value) { return moment(value.dateCreated).hour();});
	createDownloadTimesPerHourChart($('#internetHoursChart'), hoursChartData);
}

$(function() {
	var mapCanvas = $('#map-canvas');
	mapCanvas.css({height: $(window).height() - mapCanvas.offset().top - 2 + 'px'});
	$('.chart').css({height: $(window).height()});
	$(window).resize(function() {
		mapCanvas.css({height: $(window).height() - mapCanvas.offset().top - 2 + 'px'});
		$('.chart').css({height: $(window).height()});
	});

	map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
	var lastAction;

	$.get('index.php/api/neighborhoods', function(response) {
		displayNeighborhoods(JSON.parse(response));
	});
	$('#calls_button').click(function() {
		lastAction = '#calls_button';
		$.get('index.php/api/calls' + getFields(), handleReceivedCallsData);
		return false;
	});
	$('#internet_button').click(function() {
		lastAction = '#internet_button';
		var fields = getFields();
		$.get('index.php/api/internet' + getFields(), handleReceivedInternetData);
	});
	$('#sms_button').click(function() {
		lastAction = '#sms_button';
		$.get('index.php/api/sms' + getFields(), function(data) {
			loadSmsMarkers(JSON.parse(data));
		});
	});
	$('#avgTime_button').click(function() {
		lastAction = '#avgTime_button';
		$.get('index.php/api/calls/avgconnectiontime' + getFields(), function(data) {
			loadAVGCallData(JSON.parse(data));
		});
	});
	$('#avgDownloadTime_button').click(function() {
		lastAction = '#avgDownloadTime_button';
		$.get('index.php/api/avgtimeDown' + getFields(), function(data) {
			loadAVGInternetData(JSON.parse(data));
		});
	});
	$('#avgSMSTime_button').click(function() {
		lastAction = '#avgSMSTime_button';
		$.get('index.php/api/avgtimeSMS' + getFields(), function(data) {
			loadAVGSmsData(JSON.parse(data));
		});
	});
	$('#avgSignal_button').click(function() {
		lastAction = '#avgSignal_button';
		$.get('index.php/api/avgSignal' + getFields(), function(data) {
			loadAVGSignalData(JSON.parse(data));
		});
	});
	$('#failed_internet_button').click(function() {
		lastAction = '#failed_internet_button';
		$.get('index.php/api/internet/failed/all', function(data) {
			
		});
	});
	$('#avgFailed_internet_button').click(function() {
		lastAction = '#avgFailed_internet_button';
		$.get('index.php/api/internet/failed/average', function(data) {
			loadFailedInternetConnections(JSON.parse(data));
		});
	});

	// Manage Drawer
	var drawingManager = new google.maps.drawing.DrawingManager({
		drawingControl : true,
		drawingControlOptions : {
			position : google.maps.ControlPosition.TOP_CENTER,
			drawingModes : [google.maps.drawing.OverlayType.RECTANGLE]
		}
	});

	drawingManager.setMap(map);
	var oldShape = null;
	google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
		if (oldShape) {
			oldShape.setMap(null);
		}
		oldShape = event.overlay;
		var rectangle = event.overlay;
		var pos1 = rectangle.getBounds().getNorthEast();
		var pos2 = rectangle.getBounds().getSouthWest();
		var queryString = "?lat1=" + pos1.lat() + "&lon1=" + pos1.lng() + "&lat2=" + pos2.lat() + "&lon2=" + pos2.lng();
		if (document.getElementById("inputDateFrom").value != "" && document.getElementById("inputDateTo").value != "") {
			queryString += "&dateFrom=" + document.getElementById("inputDateFrom").value + "&dateTo=" + document.getElementById("inputDateTo").value;
		}
		if ($("#filterNumber").val() != "")
			queryString += "&number = " + $("#filterNumber").val();
		
		$.get('index.php/calls' + queryString, handleReceivedCallsData);
	});

	//############################################################################

	//Dates Manager

	$('#dateFrom').datetimepicker({
		format : "YYYY-MM-DD hh:mm:ss",
		language : 'es'
	});
	$('#dateTo').datetimepicker({
		format : "YYYY-MM-DD hh:mm:ss",
		language : 'es'
	});
	$("#dateFrom").on("dp.change", function(e) {
		$('#dateTo').data("DateTimePicker").setMinDate(e.date);
	});
	$("#dateTo").on("dp.change", function(e) {
		$('#dateFrom').data("DateTimePicker").setMaxDate(e.date);
	});

	$("#clearDates").click(function() {
		$("#inputDateFrom").val("");
		$("#inputDateTo").val("");
	});
	$("#reload").click(function() {
		$(lastAction).trigger("click");
	});
	$("#clearFilters").click(function() {
		$(lastAction).trigger("click");
	});	
	$("#useFilterButton").click(function() {
		$(lastAction).trigger("click");
	});	

	//##############################################################################

	//Filters Manager
	$("#numberFilterCancel").click(function() {
		$("#filterNumber").val("");
	});
	$("#numberFilterCancel").click(function() {
		$("#filterNumber").val("");
	});

	$("#clearFilters").click(function() {
		$("#filterNumber").val("");
		$("#inputDateFrom").val("");
		$("#inputDateTo").val("");

	});
	//###############################################################################
});
