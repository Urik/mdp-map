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
						fillOpacity : 0.35
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
	}
	colored = [];

}

function loadInternetMarkers(markers) {
	var data = _.map(markers, function(x) {
		return createMarkerWindowData(x.sourceNumber, x.operatorName, x.batteryLevel, x.currentSignal, x.locationLat, x.locationLon, x.dateCreated, {
			'Tiempo de descarga [ms]' : x.downloadTime
		});
	});

	loadMarkers('Test de internet', data);
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

function loadAVGTimeMarkers(AVGTimeData) {
	var markerData = _.map(AVGTimeData, function(x) {
		if (x.type == 'call')
			return {
				type : x.type,
				avgConnectionTime : x.avg_connection_time,
				avgSignal : x.avg_signal,
				neighId : x.neighborhood_id,
				neighName : x.name,
				numRegs : x.num_regs
			};
		else if (x.type == 'internet')
			return {
				type : x.type,
				avgDownloadTime : x.avg_download_time,
				avgSignal : x.avg_signal,
				neighId : x.neighborhood_id,
				neighName : x.name,
				numRegs : x.num_regs
			};
		else if (x.type == 'SMS')
			return {
				type : x.type,
				avgSendingTime : x.avg_sending_time,
				avgSignal : x.avg_signal,
				neighId : x.neighborhood_id,
				neighName : x.name,
				numRegs : x.num_regs
			};
	});

	loadAVGWindows(markerData);
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

function loadMarkers(title, totalData) {
	clearMarkers();
	if (infos.length > 0) {
		colorNeighs('darkblue');
		clearInfos();
	}
	$.each(totalData, function(i, data) {
		(function() {
			var marker = new google.maps.Marker({
				position : new google.maps.LatLng(data.callerLat, data.callerLon),
				title : title
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

	//var contentHTML = '<table><tr><th><b>Barrio</b></th><th><b>Promedio</b></th><th><b>Nro de Reg</b></th></tr>';

	$.each(totalData, function(i, data) {
		(function() {

			var infoWindow = new google.maps.InfoWindow();

			var contentString = '<p style="color:red"><b>Barrio ' + data.neighName + '</b></p>';

			if (data.type == 'call')
				contentString += '<p>Tiempo Promedio de Conexión: ' + parseFloat(data.avgConnectionTime).toFixed(2) + ' segs</p>';
			else if (data.type == 'internet')
				contentString += '<p>Tiempo Promedio de Descarga: ' + parseFloat(data.avgDownloadTime / 1000).toFixed(2) + ' segs</p>';
			else
				contentString += '<p>Tiempo Promedio de Envío: ' + parseFloat(data.avgSendingTime / 1000).toFixed(2) + ' segs</p>';

			contentString += '<p>Promedio de señal: ' + parseFloat(data.avgSignal).toFixed(2) + '</p>';
			contentString += '<p>Cantidad de registros: ' + data.numRegs + '</p>';

			if ((data.type == 'call' && data.avgConnectionTime > 0) || (data.type == 'internet' && data.avgDownloadTime > 0) || (data.type == 'SMS' && data.avgSendingTime > 0)) {
				neighArray[data.neighId].setOptions({
					fillColor : 'red'
				});
				colored.push(neighArray[data.neighId]);
			}

			infoWindow.setContent(contentString);
			infoWindow.setPosition(polygonCenter(neighArray[data.neighId]));
			infos.push(infoWindow);

			var showInfo = function(event) {
				infoWindow.setPosition(event.latLng);
				infoWindow.open(map);
				//document.getElementById("neighTable").innerHTML += contentString;

			};

			google.maps.event.addListener(neighArray[data.neighId], 'click', showInfo);

		})();
	});

}

function getColor(data) {
	var bestValue;
	var worstValue;
	var rgb = 'rgb(';

	if (data.type == 'call') {
		bestValue = 5;
		worstValue = 15;
		rgb += parseInt(data.avgConnectionTime * 17) + ',';
		//red value
		rgb += parseInt(204 / data.avgConnectionTime) + ',0)';
		//green value

	}

	return rgb;
}

function getDateString() {
	if (document.getElementById("inputDateFrom").value != "" && document.getElementById("inputDateTo").value != "") {
		var dateString = "?dateFrom=" + document.getElementById("inputDateFrom").value + "&dateTo=" + document.getElementById("inputDateTo").value;
		return dateString;
	} else
		return "";
}

$(function() {
	map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
	var lastAction;

	$.get('index.php/neighborhoods', function(response) {
		displayNeighborhoods(JSON.parse(response));
	});
	$('#calls_button').click(function() {
		lastAction = '#calls_button';
		$.get('index.php/calls' + getDateString(), function(data) {
			loadCallsMarkers(JSON.parse(data));
		});
		return false;
	});
	$('#internet_button').click(function() {
		lastAction = '#internet_button';
		$.get('index.php/internet' + getDateString(), function(data) {
			loadInternetMarkers(JSON.parse(data));
		});
	});
	$('#sms_button').click(function() {
		lastAction = '#sms_button';
		$.get('index.php/sms' + getDateString(), function(data) {
			loadSmsMarkers(JSON.parse(data));
		});
	});
	$('#avgTime_button').click(function() {
		lastAction = '#avgTime_button';
		$.get('index.php/avgtime' + getDateString(), function(data) {
			loadAVGTimeMarkers(JSON.parse(data));
		});
	});
	$('#avgDownloadTime_button').click(function() {
		lastAction = '#avgDownloadTime_button';
		$.get('index.php/avgtimeDown' + getDateString(), function(data) {
			loadAVGTimeMarkers(JSON.parse(data));
		});
	});
	$('#avgSMSTime_button').click(function() {
		lastAction = '#avgSMSTime_button';
		$.get('index.php/avgtimeSMS' + getDateString(), function(data) {
			loadAVGTimeMarkers(JSON.parse(data));
		});
	});

	// Manage Drawer
	var drawingManager = new google.maps.drawing.DrawingManager({
		drawingMode : google.maps.drawing.OverlayType.RECTANGLE,
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
		$.get('index.php/calls' + queryString, function(data) {
			loadCallsMarkers(JSON.parse(data));
		});
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
	//##############################################################################
});
