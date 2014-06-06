<?php
include_once "DB/MapDB.php";
$result = getNeighborhoods();
$zoneResult = getZones();
if (isset($_GET["view"]) && $_GET["view"] == 1)
	$callResult = getCalls();
if (isset($_GET["view"]) && ($_GET["view"] == 2 || $_GET["view"] == 4))
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
		<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=geometry&sensor=false"></script>
		<script src="js/markerwithlabel.js" type="text/javascript"></script>
		<script>
		
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

echo "var zones = new Array(); "; //array for map zones polygons
echo "var zoneArray1 = new Array(); "; //arrays for neighborhoods in zones
echo "var zoneArray2 = new Array(); ";
echo "var zoneArray3 = new Array(); ";
echo "var zoneArray4 = new Array(); ";
echo "var zoneArray5 = new Array(); ";
echo "var neighArray = new Array(); ";
echo "var callArray = new Array(); ";
getItemCalls();

if ($result) {//If neighborhood information is available
	displayNeighborhoods($result);
}

if ($zoneResult) {//If neighborhood information is available
	loadZones($zoneResult);
}

?>


/*
for(var index = 0; index<callArray.length; index++){
	var n = 0;
	while(n<neighArray.length && !google.maps.geometry.poly.containsLocation(callArray[index].latlng, neighArray[n]))
		n++;
	if(n<neighArray.length){ //está en un barrio
		n++;
		console.error("UPDATE tesis.call SET neighborhood_id = " + n + " WHERE id = " + callArray[index].id + ";");
		//alert(callArray[index].id + " está en " + n);
		
		//hacer update de callArray[index].id en la zone_id n;
		document.getElementById("lenght").innerHTML = document.getElementById("lenght").innerHTML + "UPDATE tesis.call SET neighborhood_id = " + n + " WHERE id = " + callArray[index].id + "; "; 
	}
	//else alert("indeterminado");
}

*/


<?php

if (isset($callResult) && $callResult) {
		displayMarkers($_GET["view"], $callResult);
}

if (isset($internetResult) && $internetResult) {
		if($_GET["view"] == 2)
			displayMarkers($_GET["view"], $internetResult);
		else zoning('internet'); 
}

if (isset($smsResult) && $smsResult) {
		displayMarkers($_GET["view"], $smsResult);
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
			<!--<a href="index.php?view=4">Internet por Zonas</a>-->
		</div>
		<div id="map-canvas"></div>
		<div id="lenght"></div>

	</body>
</html>