<?php
include_once "DB/MapDB.php";
$result = getNeighborhoods();
if (isset($_GET["view"]) && $_GET["view"] == 1)
	$callResult = getCalls();
if (isset($_GET["view"]) && $_GET["view"] == 2)
	$internetResult = getInternet();
if (isset($_GET["view"]) && $_GET["view"] == 3)
	$smsResult = getSMS();
?>

<!DOCTYPE html>

<html>
	<head>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<meta charset="utf-8">
		<title>Polygon Arrays</title>
		<style>
			html, body, #map-canvas {
				height: 100%;
				margin: 0px;
				padding: 0px
			}
			.labels {
				color: red;
				background-color: white;
				font-family: "Lucida Grande", "Arial", sans-serif;
				font-size: 10px;
				font-weight: bold;
				text-align: center;
				width: 40px;
				border: 2px solid black;
				white-space: nowrap;
			}
		</style>

		<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
		<script src="js/markerwithlabel.js" type="text/javascript"></script>
		<script>
			// This example creates a simple polygon representing the Bermuda Triangle.
// When the user clicks on the polygon an info window opens, showing
// information about the polygon's coordinates.

var map;
var infoWindow;

function initialize() {
var mapOptions = {
zoom: 12,
center: new google.maps.LatLng(-38,-57.55),
mapTypeId: google.maps.MapTypeId.TERRAIN
};

map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

<?php

if ($result) {//If neighborhood information is available
	$i = 0;
	$lastNeigh = "";
	while ($neigh = $result -> fetch_object()) {
		if ($lastNeigh != $neigh -> name) {//change of polygon
			if ($i) {//unless first loop
				renderNeighborhood($i);
			
				
			}
			$lastNeigh = $neigh -> name;
			$i = $i + 1;
			echo "var neighborhood" . $i . "; ";
			echo "var neighborhoodCoords" . $i . " = [";
			$first = 1;
		}
		if (!$first)
			echo ", ";
		else
			$first = 0;
		echo "new google.maps.LatLng(" . $neigh -> latitude . " , " . $neigh -> longitude . ")";
	}

	renderNeighborhood($i);
}

if (isset($_GET["view"]) && $_GET["view"] == 1) {
	if ($callResult) {//If Calldata available
		$i = 1;
		while ($call = $callResult -> fetch_object()) {
			echo "var marker" . $i . " = new google.maps.Marker({ ";
			echo "position: new google.maps.LatLng(" . $call -> locationLat . " , " . $call -> locationLon . "), ";
			echo "title:'Hello World!' ";
			echo "}); ";
			echo "marker" . $i . ".setMap(map); ";

			echo "var contentString" . $i . " = '<p>Número orígen: " . $call -> sourceNumber . "</p><p>Operador: " . $call -> operatorName . "</p><p>Nivel de Batería: " . $call -> batteryLevel . "</p><p>Nivel de Señal: " . $call -> currentSignal . "</p>'; ";
			echo "var infowindow" . $i . " = new google.maps.InfoWindow({ ";
			echo "content: contentString" . $i . " ";
			echo "}); ";
			echo " google.maps.event.addListener(marker" . $i . ", 'click', function() { ";
			echo "infowindow" . $i . ".open(map,marker" . $i . "); ";
			echo " }); ";

			$i = $i + 1;

		}

	}
}

if (isset($_GET["view"]) && $_GET["view"] == 2) {
	if ($internetResult) {//If Calldata available
		$i = 1;
		while ($internet = $internetResult -> fetch_object()) {
			echo "var marker" . $i . " = new google.maps.Marker({ ";
			echo "position: new google.maps.LatLng(" . $internet -> locationLat . " , " . $internet -> locationLon . "), ";
			echo "title:'Hello World!' ";
			echo "}); ";
			echo "marker" . $i . ".setMap(map); ";

			echo "var contentString" . $i . " = '<p>Número orígen: " . $internet -> sourceNumber . "</p><p>Operador: " . $internet -> operatorName . "</p><p>Nivel de Batería: " . $internet -> batteryLevel . "</p><p>Nivel de Señal: " . $internet -> currentSignal . "</p><p>Tiempo de Descarga: " . $internet -> downloadTime . " ms</p>'; ";
			echo "var infowindow" . $i . " = new google.maps.InfoWindow({ ";
			echo "content: contentString" . $i . " ";
			echo "}); ";
			echo " google.maps.event.addListener(marker" . $i . ", 'click', function() { ";
			echo "infowindow" . $i . ".open(map,marker" . $i . "); ";
			echo " }); ";

			$i = $i + 1;

		}

	}
}

if (isset($_GET["view"]) && $_GET["view"] == 3) {
	if ($smsResult) {//If Calldata available
		$i = 1;
		while ($sms = $smsResult -> fetch_object()) {
			echo "var marker" . $i . " = new google.maps.Marker({ ";
			echo "position: new google.maps.LatLng(" . $sms -> locationLat . " , " . $sms -> locationLon . "), ";
			echo "title:'Hello World!' ";
			echo "}); ";
			echo "marker" . $i . ".setMap(map); ";

			echo "var contentString" . $i . " = '<p>Número orígen: " . $sms -> sourceNumber . "</p><p>Operador: " . $sms -> operatorName . "</p><p>Nivel de Batería: " . $sms -> batteryLevel . "</p><p>Nivel de Señal: " . $sms -> currentSignal . "</p><p>Tiempo de Envío: " . $sms -> sendingTime . " ms</p>'; ";
			echo "var infowindow" . $i . " = new google.maps.InfoWindow({ ";
			echo "content: contentString" . $i . " ";
			echo "}); ";
			echo " google.maps.event.addListener(marker" . $i . ", 'click', function() { ";
			echo "infowindow" . $i . ".open(map,marker" . $i . "); ";
			echo " }); ";

			$i = $i + 1;

		}

	}
}
?>
	}

	google.maps.event.addDomListener(window, 'load', initialize);

		</script>
	</head>
	<body>
		<div>
			<a href="index.php?view=1">Ver llamadas</a>
			<a href="index.php?view=2">Ver Internet</a>
			<a href="index.php?view=3">Ver SMS</a>
		</div>
		<div id="map-canvas"></div>

	</body>
</html>

<!--echo " var neighMarker".$i." = new MarkerWithLabel({ ";
       			echo "position: neighborhood".$i.".getBounds().getCenter(), ";
       			echo "map: map, ";
       			echo "labelContent: '".$neigh->name."' ," ;
       			echo "labelAnchor: new google.maps.Point(22, 0), ";
       			echo "labelClass: 'labels', "; // the CSS class for the label
       			echo "labelStyle: {opacity: 0.75} ";
    			echo " }); "; -->