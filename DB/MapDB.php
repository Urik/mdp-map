<?php

include_once 'DB/DBConnection.php';

function getNeighborhoods(){
    $con = connectDB();
  	if ($result = $con->query("SELECT n.id, n.name, c.latitude, c.longitude, n.zone_id FROM neighborhood AS n INNER JOIN coordinate AS c ON n.id = c.neighborhood_id ORDER BY n.id"))
         return $result;
    disconnectDB($con);
         
   
}

function getZones(){
    $con = connectDB();
  	if ($result = $con->query("SELECT z.name, c.latitude, c.longitude FROM zones AS z INNER JOIN zone_coor AS c ON z.id = c.zone_id"))
         return $result;
    disconnectDB($con);
         
   
}

function getCoordinates($neigh_id){
    $con = connectDB();
    if ($result = $con->query("SELECT * FROM coordinate WHERE neighborhood_id = ".$neigh_id))
         return $result;
    else return 0;
    disconnectDB($con);
         
}

function displayNeighborhoods($result){
	$i = 0;
	$lastNeigh = "";
	while ($neigh = $result -> fetch_object()) {
		$zone = $neigh -> zone_id;
		if ($lastNeigh != $neigh -> name) {//change of polygon
			if ($i) {//unless first loop
				renderNeighborhood($i,$zone);				
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

	renderNeighborhood($i,$zone);
}

function renderNeighborhood($i,$zone){
			 echo "]; ";
			 echo "neighborhood".$i."= new google.maps.Polygon({
									paths: neighborhoodCoords".$i.",
									strokeColor: '#000000',
									strokeOpacity: 0.8,
									strokeWeight: 3,
									fillColor: '#' + (0x1000000 + Math.random() * 0xFFFFFF).toString(16).substr(1,6),
									fillOpacity: 0.35
									}); ";
			echo "neighborhood".$i.".setMap(map); ";
			echo "zoneArray".$zone.".push(neighborhood".$i."); ";
			echo "neighArray.push(neighborhood".$i."); ";
			
	
			
}


function getCalls(){
	$con = connectDB();
  	if ($result = $con->query("SELECT c.sourceNumber, c.operatorName, c.batteryLevel, c.currentSignal,  c.locationLat, c.locationLon, m.connectionTime, m.ReceiverSignal FROM tesis.call c
INNER JOIN matched_calls m ON c.id = m.OutgoingCallId"))
         return $result;
    disconnectDB($con);
}

function getInternet(){
	$con = connectDB();
  	if ($result = $con->query("SELECT * FROM internet"))
         return $result;
    disconnectDB($con);
}

function getSMS(){
	$con = connectDB();
  	if ($result = $con->query("SELECT * FROM sms"))
         return $result;
    disconnectDB($con);
}

function displayMarkers($view, $result){
		$i = 1;
		while ($obj = $result -> fetch_object()) {
			echo "var marker" . $i . " = new google.maps.Marker({ ";
			echo "position: new google.maps.LatLng(" . $obj -> locationLat . " , " . $obj -> locationLon . "), ";
			echo "title:'Hello World!' ";
			echo "}); ";
			echo "marker" . $i . ".setMap(map); ";

			echo "var contentString" . $i . " = '<p>Número orígen: " . $obj -> sourceNumber . "</p><p>Operador: " . $obj -> operatorName . "</p><p>Nivel de Batería: " . $obj -> batteryLevel . "</p><p>Señal del Emisor: " . $obj -> currentSignal . "</p>";
			if($view == 1){
				echo "<p>Señal del Destinatario: " . $obj -> ReceiverSignal . " ms</p>";
				echo "<p>Tiempo de Conexión: " . $obj -> ConnectionTime . " seg</p>";
			}
			elseif($view == 2)
				echo "<p>Tiempo de Descarga: " . $obj -> downloadTime . " ms</p>";
			elseif ($view == 3)
				echo "<p>Tiempo de Envío: " . $obj -> sendingTime . " ms</p>";
			echo "'; ";
			echo "var infowindow" . $i . " = new google.maps.InfoWindow({ ";
			echo "content: contentString" . $i . " ";
			echo "}); ";
			echo " google.maps.event.addListener(marker" . $i . ", 'click', function() { ";
			echo "infowindow" . $i . ".open(map,marker" . $i . "); ";
			echo " }); ";
			
			$i = $i + 1;
			

		}
		
	
}

function loadZones($result){
	$i = 0;
	$lastZone = "";
	while ($zone = $result -> fetch_object()) {
		if ($lastZone != $zone -> name) {//change of polygon
			if ($i) {//unless first loop
				renderZone($i);				
			}
			$lastZone = $zone -> name;
			$i = $i + 1;
			echo "var zone" . $i . "; ";
			echo "var zoneCoords" . $i . " = [";
			$first = 1;
		}
		if (!$first)
			echo ", ";
		else
			$first = 0;
		echo "new google.maps.LatLng(" . $zone -> latitude . " , " . $zone -> longitude . ")";
	}

	renderZone($i);
}


function renderZone($i){
			 echo "]; ";
			 echo "zone".$i."= new google.maps.Polygon({
									paths: zoneCoords".$i.",
									strokeColor: '#000000',
									strokeOpacity: 0.8,
									strokeWeight: 3,
									fillColor: '#' + (0x1000000 + Math.random() * 0xFFFFFF).toString(16).substr(1,6),
									fillOpacity: 0.35
									}); ";			
			echo "zones.push(zone".$i."); ";			
}

function zoning($table){
	if($table == "internet")
		$result = getInternet();
	while ($obj = $result -> fetch_object()) {
		
	}
	
}

function getItemCalls(){
	$con = connectDB();
  	if ($result = $con->query("SELECT * FROM internet where locationLat<>0 AND locationLon <> 0 ORDER BY id")){
  			$i = 0;
  			while ($item = $result -> fetch_object()) {
				echo "var item = { ";
				echo "latlng: new google.maps.LatLng(" . $item -> locationLat . " , " . $item -> locationLon . "), ";
				echo "id : ".$item->id;
				echo "}; ";
				
				echo "callArray.push(item); ";
				$i = $i +1;
			}
		
  	}
     
    disconnectDB($con);
}
?>

