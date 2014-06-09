<?php

include_once 'DB/DBConnection.php';

function getNeighborhoods(){
    $con = connectDB();
    $response = [];
  	if ($result = $con->query("SELECT n.id AS id, n.name AS name, c.latitude AS latitude, c.longitude AS longitude, n.zone_id AS zone_id FROM neighborhood AS n INNER JOIN coordinate AS c ON n.id = c.neighborhood_id ORDER BY n.id"))
  		while ($item = $result->fetch_object()) {
  			$response[] = [
  				'id' => $item->id,
  				'name' => $item->name,
  				'lat' => $item->latitude,
  				'lon' => $item->longitude,
  				'zone_id' => $item->zone_id
  			];
  		}
    disconnectDB($con); 
    return $response;
}

function getZones() {
  $con = connectDB();
  $response = [];
	if ($result = $con->query("SELECT z.name, c.latitude, c.longitude FROM zones AS z INNER JOIN zone_coor AS c ON z.id = c.zone_id")) {
		while($item = $result->fetch_assoc()) {
			$response[] = $item;
		}
  }
  disconnectDB($con);
  return $response;
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
	$response = [];
	$con = connectDB();
	if ($result = $con->query("SELECT m.CallerNumber AS caller_number, m.CallerOperatorName as caller_operator_name, m.CallerBatteryLevel as caller_battery_level, m.CallerSignal AS caller_signal, m.CallerLat AS caller_lat, m.CallerLon AS caller_lon, m.connectionTime AS connection_time, m.ReceiverSignal AS receiver_signal FROM matched_calls m"))
       while ($item = $result->fetch_assoc()) {
       	$response[] = $item;
       }
  disconnectDB($con);
  return $response;
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

function getInternetTests(){
	$tests = [];
	$con = connectDB();
  	if ($result = $con->query("SELECT * FROM internet where locationLat<>0 AND locationLon <> 0 ORDER BY id")){
			while ($item = $result -> fetch_object()) {
				$tests[] = [
					'id' => $item->id,
					'lat' => $item->locationLat,
					'lon' => $item->locationLon
				];						
  		}
  	}
     
    disconnectDB($con);

    return $tests;
}

?>

