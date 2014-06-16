<?php

include_once 'DB/DBConnection.php';

function getNeighborhoods() {
	$con = connectDB();
	$response = Array();
	if ($result = $con -> query("SELECT n.id AS id, n.name AS name, c.latitude AS latitude, c.longitude AS longitude, n.zone_id AS zone_id FROM neighborhood AS n INNER JOIN coordinate AS c ON n.id = c.neighborhood_id ORDER BY n.id"))
		while ($item = $result -> fetch_object()) {
			$response[] = array('id' => $item -> id, 'name' => utf8_encode($item -> name), 'lat' => $item -> latitude, 'lon' => $item -> longitude, 'zone_id' => $item -> zone_id);
		}
	disconnectDB($con);
	return $response;
}

function getZones() {
	$con = connectDB();
	$response = Array();
	;
	if ($result = $con -> query("SELECT z.name, c.latitude, c.longitude FROM zones AS z INNER JOIN zone_coor AS c ON z.id = c.zone_id")) {
		while ($item = $result -> fetch_assoc()) {
			$response[] = encodeArrayToUtf($item);
		}
	}
	disconnectDB($con);
	return $response;
}

function getCoordinates($neigh_id) {
	$con = connectDB();
	if ($result = $con -> query("SELECT * FROM coordinate WHERE neighborhood_id = " . $neigh_id))
		return $result;
	else
		return 0;
	disconnectDB($con);

}

function displayNeighborhoods($result) {
	$i = 0;
	$lastNeigh = "";
	while ($neigh = $result -> fetch_object()) {
		$zone = $neigh -> zone_id;
		if ($lastNeigh != $neigh -> name) {//change of polygon
			if ($i) {//unless first loop
				renderNeighborhood($i, $zone);
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

	renderNeighborhood($i, $zone);
}

function renderNeighborhood($i, $zone) {
	echo "]; ";
	echo "neighborhood" . $i . "= new google.maps.Polygon({
									paths: neighborhoodCoords" . $i . ",
									strokeColor: '#000000',
									strokeOpacity: 0.8,
									strokeWeight: 3,
									fillColor: '#' + (0x1000000 + Math.random() * 0xFFFFFF).toString(16).substr(1,6),
									fillOpacity: 0.35
									}); ";
	echo "neighborhood" . $i . ".setMap(map); ";
	echo "zoneArray" . $zone . ".push(neighborhood" . $i . "); ";
	echo "neighArray.push(neighborhood" . $i . "); ";

}

function getCalls($lat1, $lon1, $lat2, $lon2, $dateFrom, $dateTo, $number) {
	$response = Array();
	$con = connectDB();
	$sql = "SELECT m.CallerNumber AS caller_number, m.CallerOperatorName as caller_operator_name, m.CallerBatteryLevel as caller_battery_level, m.CallerSignal AS caller_signal, m.CallerLat AS caller_lat, m.CallerLon AS caller_lon, m.connectionTime AS connection_time, m.ReceiverSignal AS receiver_signal, callerTime AS caller_time FROM matched_calls m ";
	$whereClause = " WHERE  m.CallerNumber IS NOT NULL AND m.CallerLat <> 0 AND  m.CallerLon <> 0";
	if (!is_null($lat1) && !is_null($lon1) && !is_null($lat2) && !is_null($lon2)) {
		$lineString = 'LINESTRING(' . $lat1 . ' ' . $lon1 . ', ' . $lat2 . ' ' . $lon2 . ')';
		$whereClause .= ' AND MBRContains(GeomFromText(\'' . $lineString . '\'), m.OutgoingGeom)';

	}

	if (!is_null($dateFrom) && !is_null($dateTo))
		$whereClause .= " AND m.CallerTime BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "'";
	
	if(!is_null($number))
		$whereClause .= " AND m.CallerNumber = '" . $number . "'";

	$sql .= $whereClause;

	if ($result = $con -> query($sql))
		while ($item = $result -> fetch_assoc()) {
			$response[] = $item;
		}
	disconnectDB($con);
	return $response;
}

function getSMS($dateFrom, $dateTo, $number) {
	$response = Array();
	$con = connectDB();

	$sql = "SELECT * FROM sms ";
	$whereClause = " WHERE neighborhood_id IS NOT NULL ";

	if (!is_null($dateFrom) && !is_null($dateTo))
		$whereClause .= "AND dateCreated BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "'";

	if(!is_null($number))
		$whereClause .= " AND sourceNumber = '" . $number . "'";


	$sql .= $whereClause;
	if ($result = $con -> query($sql)) {
		while ($item = $result -> fetch_assoc()) {
			$response[] = encodeArrayToUtf($item);
		}
	}
	disconnectDB($con);
	return $response;
}

function displayMarkers($view, $result) {
	$i = 1;
	while ($obj = $result -> fetch_object()) {
		echo "var marker" . $i . " = new google.maps.Marker({ ";
		echo "position: new google.maps.LatLng(" . $obj -> locationLat . " , " . $obj -> locationLon . "), ";
		echo "title:'Hello World!' ";
		echo "}); ";
		echo "marker" . $i . ".setMap(map); ";

		echo "var contentString" . $i . " = '<p>Número orígen: " . $obj -> sourceNumber . "</p><p>Operador: " . $obj -> operatorName . "</p><p>Nivel de Batería: " . $obj -> batteryLevel . "</p><p>Señal del Emisor: " . $obj -> currentSignal . "</p>";
		if ($view == 1) {
			echo "<p>Señal del Destinatario: " . $obj -> ReceiverSignal . " ms</p>";
			echo "<p>Tiempo de Conexión: " . $obj -> ConnectionTime . " seg</p>";
		} elseif ($view == 2)
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

function loadZones($result) {
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

function renderZone($i) {
	echo "]; ";
	echo "zone" . $i . "= new google.maps.Polygon({
									paths: zoneCoords" . $i . ",
									strokeColor: '#000000',
									strokeOpacity: 0.8,
									strokeWeight: 3,
									fillColor: '#' + (0x1000000 + Math.random() * 0xFFFFFF).toString(16).substr(1,6),
									fillOpacity: 0.35
									}); ";
	echo "zones.push(zone" . $i . "); ";
}

/* Creo que no sirve
 * function zoning($table){
 if($table == "internet")
 $result = getInternet();
 while ($obj = $result -> fetch_object()) {

 }

 }*/

function getInternetTests($dateFrom, $dateTo, $number) {
	$tests = Array();
	$con = connectDB();

	$sql = "SELECT * FROM internet ";
	$whereClause = " WHERE neighborhood_id IS NOT NULL AND downloadTime <> 0";

	if (!is_null($dateFrom) && !is_null($dateTo))
		$whereClause .= " AND dateCreated BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "'";

	if(!is_null($number))
		$whereClause .= " AND sourceNumber = '" . $number . "'";

	$sql .= $whereClause;

	if ($result = $con -> query($sql)) {
		while ($item = $result -> fetch_assoc()) {
			$tests[] = encodeArrayToUtf($item);
		}
	}

	disconnectDB($con);

	return $tests;
}

function getAVGTime($type, $dateFrom, $dateTo, $number) {
	$response = Array();
	$con = connectDB();
	$sql = "";

	if ($type == "call") {
		$sql = "SELECT 'call' as type, AVG(m.connectionTime) as avg_connection_time, AVG(m.callerSignal) as avg_signal, AVG(m.ReceiverSignal) as avg_rec_signal, COUNT(c.neighborhood_id) as num_regs, c.neighborhood_id as neighborhood_id , n.name FROM tesis.call c
		INNER JOIN matched_calls m ON c.id = m.OutgoingCallId
		INNER JOIN neighborhood n ON c.neighborhood_id = n.id
		WHERE c.neighborhood_id IS NOT NULL ";
		$index = "c";
	} elseif ($type == "internet") {
		$sql = "SELECT 'internet' as type, AVG(i.downloadTime) as avg_download_time, AVG(i.currentSignal) as avg_signal, COUNT(i.neighborhood_id) as num_regs, i.neighborhood_id as neighborhood_id , n.name FROM internet i
		INNER JOIN neighborhood n ON i.neighborhood_id = n.id
		WHERE i.neighborhood_id IS NOT NULL AND i.downloadTime <> 0 ";
		$index = "i";
	} elseif ($type == "SMS") {
		$sql = "SELECT 'SMS' as type, AVG(s.sendingTime) as avg_sending_time, AVG(s.currentSignal) as avg_signal, COUNT(s.neighborhood_id) as num_regs, s.neighborhood_id as neighborhood_id , n.name FROM sms s
		RIGHT JOIN neighborhood n ON s.neighborhood_id = n.id
		WHERE s.neighborhood_id IS NOT NULL ";
		$index = "s";
	} elseif ($type == "signal"){
		$sql = "SELECT 'signal' as type, AVG(si.currentSignal) as avg_signal, COUNT(si.neighborhood_id) as num_regs, si.neighborhood_id as neighborhood_id , n.name FROM tesis.call si
		RIGHT JOIN neighborhood n ON si.neighborhood_id = n.id
		WHERE si.neighborhood_id IS NOT NULL ";
		$index = "si";		
	}

	if (!is_null($dateFrom) && !is_null($dateTo))
		$sql .= " AND " . $index . ".dateCreated BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "'";

	if(!is_null($number))
		$sql .= " AND " . $index . ".sourceNumber = '" . $number . "'";

	$sql .= " GROUP BY " . $index . ".neighborhood_id";

	$result = $con -> query($sql);

	if ($result)
		while ($item = $result -> fetch_assoc()) {
			$response[] = encodeArrayToUtf($item);
		};

	disconnectDB($con);
	return $response;

}

function encodeArrayToUtf($array) {
	$response = array();
	foreach ($array as $key => $value) {
		if (is_string($value)) {
			$response[$key] = utf8_encode($value);
		} else {
			$response[$key] = $value;
		}
	}
	
	return $response;
}
?>

