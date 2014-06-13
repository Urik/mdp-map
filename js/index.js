var ENDPOINT = 'http://localhost/mdp-map/index.php/';
var map;
var mapOptions = {
	zoom: 12,
	center: new google.maps.LatLng(-38, -57.55),
	mapTypeId: google.maps.MapTypeId.TERRAIN
};
var markers = [];

var zones =[]; //array for map zones polygons

var neighArray = [];
var callArray = [];

function displayNeighborhoods(neighborhoods) {
	var lastNeigh = "";
	var groupedNeighborhoodsByZone = _.groupBy(neighborhoods, 'zone_id');
	for(var zone_id in groupedNeighborhoodsByZone) {
		(function(){
			zones[zone_id] = [];
			var neighs = groupedNeighborhoodsByZone[zone_id];
			var groupedNeighborhoodsByName = _.groupBy(neighs, 'name');
			for(var name in groupedNeighborhoodsByName) {
				(function() {
					var neighborhood = groupedNeighborhoodsByName[name];
					var coordinates = _.map(neighborhood, function(x) { return new google.maps.LatLng(x.lat, x.lon); });
					var neighborhoodPolygon = new google.maps.Polygon({
						paths: coordinates,
						strokeColor: '#000000',
						strokeOpacity: 0.8,
						strokeWeight: 3,
						fillColor: '#' + (0x1000000 + Math.random() * 0xFFFFFF).toString(16).substr(1,6),
						fillOpacity: 0.35
					});
					neighborhoodPolygon.setMap(map);
					neighArray.push(neighborhoodPolygon);
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

function loadInternetMarkers(markers) {
	var data = _.map(markers, function(x) {
		return createMarkerWindowData(
			x.sourceNumber,
			x.operatorName,
			x.batteryLevel,
			x.currentSignal,
			x.locationLat,
			x.locationLon,
			x.dateCreated,
			{
				'Tiempo de descarga [ms]': x.downloadTime
			});
	});

	loadMarkers('Test de internet', data);
}

function loadSmsMarkers(smsData) {
	var data = _.map(smsData, function(x) {
		return createMarkerWindowData(
			x.sourceNumber,
			x.operatorName,
			x.batteryLevel,
			x.currentSignal,
			x.locationLat,
			x.locationLon,
			x.dateCreated,
			{
				'Tiempo de envio [ms]': x.sendingTime
			});
	});

	loadMarkers('Envio de SMS', data);
}

function loadCallsMarkers(callsData) {
	var markerData = _.map(callsData, function(x) {
		return createMarkerWindowData(
				x.caller_number,
				x.caller_operator_name,
				x.caller_battery_level,
				x.caller_signal,
				x.caller_lat,
				x.caller_lon,
				x.caller_time,
				{
					'Señal del Destinatario': x.receiver_signal,
					'Tiempo de Conexion': x.connection_time
				});
	});

	loadMarkers('Llamada', markerData);
}

function createMarkerWindowData(callerNumber, operatorName, callerBatteryLevel, callerSignal, lat, lon, date, customData) {
	return {
		callerLat: lat,
		callerLon: lon,
		sourceNumber: callerNumber,
		operatorName: operatorName,
		batteryLevel: callerBatteryLevel,
		sourceSignal: callerSignal,
		date: date,
		windowContent: customData
	};
}

function loadMarkers(title, totalData) {
	clearMarkers();
	$.each(totalData, function(i, data){
		(function() {
			var marker = new google.maps.Marker({
				position: new google.maps.LatLng(data.callerLat, data.callerLon),
				title: title
			});
			marker.setMap(map);
			markers.push(marker);
			var contentString = '<p>Numero de origen: '+ data.sourceNumber + '</p>';
			contentString += '<p>Fecha de evento: ' + data.date + '</p>';
			contentString += '<p>Operador: ' + data.operatorName + '</p>';
			contentString += '<p>Nivel de bateria: ' + data.batteryLevel + '</p>';
			contentString += '<p>Señal del emisor: ' + data.sourceSignal + '</p>';
			for(var key in data.windowContent) {
				contentString += '<p>' + key + ': ' + data.windowContent[key] + '</p>';
			}

			var infoWindow = new google.maps.InfoWindow({
				content: contentString
			});
			google.maps.event.addListener(marker, 'click', function() {
				infoWindow.open(map, marker);
			});
		})();
	});
}

$(function () {
	map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
	$.get('index.php/neighborhoods', function(response) {
		displayNeighborhoods(JSON.parse(response));
	});
	$('#calls_button').click(function() {
		$.get('index.php/calls', function(data) {
			loadCallsMarkers(JSON.parse(data));
		});
		return false;
	});
	$('#internet_button').click(function() {
		$.get('index.php/internet', function(data) {
			loadInternetMarkers(JSON.parse(data));
		});
	});
	$('#sms_button').click(function() {
		$.get('index.php/sms', function(data) {
			loadSmsMarkers(JSON.parse(data));
		});
	});

	var drawingManager = new google.maps.drawing.DrawingManager({
		drawingMode: google.maps.drawing.OverlayType.RECTANGLE,
		drawingControl: true,
		drawingControlOptions: {
		  position: google.maps.ControlPosition.TOP_CENTER,
		  drawingModes: [
		    google.maps.drawing.OverlayType.RECTANGLE
		  ]
		}
	});

  	drawingManager.setMap(map);
  	google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
		var rectangle = event.overlay;
		var pos1 = rectangle.getBounds().getNorthEast();
		var pos2 = rectangle.getBounds().getSouthWest();
		var queryString = "?lat1=" + pos1.lat() + "&lon1=" + pos1.lng() + "&lat2=" + pos2.lat() + "&lon2=" + pos2.lng();
		$.get('index.php/calls' + queryString, function(data) {
			loadCallsMarkers(JSON.parse(data));
		});
	});
});
