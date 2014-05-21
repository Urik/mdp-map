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

echo "var zoneArray1 = new Array(); ";
echo "var zoneArray2 = new Array(); ";
echo "var zoneArray3 = new Array(); ";
echo "var zoneArray4 = new Array(); ";
echo "var zoneArray5 = new Array(); ";

if ($result) {//If neighborhood information is available
	displayNeighborhoods($result);
}

if (isset($_GET["view"]) && $_GET["view"] == 1) {
	if ($callResult) {//If Calldata available
		displayMarkers($_GET["view"], $callResult);
	}
}

if (isset($_GET["view"]) && $_GET["view"] == 2) {
	if ($internetResult) {//If Calldata available
		displayMarkers($_GET["view"], $internetResult);
	}
}

if (isset($_GET["view"]) && $_GET["view"] == 3) {
	if ($smsResult) {//If Calldata available
		displayMarkers($_GET["view"], $smsResult);
	}
}
?>

 document.getElementById('lenght').innerHTML= zoneArray1.length +" "+ zoneArray2.length +" "+ zoneArray3.length +" "+ zoneArray4.length +" "+ zoneArray5.length; 

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
		<div id="lenght"></div>

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