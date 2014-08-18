<?php

include_once 'DB/DBConnection.php';
include_once 'underscore.php';

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
	$whereClause .= getPositionBasedWhereClause($lat1, $lon1, $lat2, $lon2);
	$whereClause .= getDateBasedWhereClause($dateFrom, $dateTo);
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

function getPositionBasedWhereClause($lat1, $lon1, $lat2, $lon2) {
	$whereClause = "";
	if (!is_null($lat1) && !is_null($lon1) && !is_null($lat2) && !is_null($lon2)) {
		$lineString = 'LINESTRING(' . $lat1 . ' ' . $lon1 . ', ' . $lat2 . ' ' . $lon2 . ')';
		$whereClause .= ' AND MBRContains(GeomFromText(\'' . $lineString . '\'), m.OutgoingGeom)';
	}

	return $whereClause;
}

function getDateBasedWhereClause($dateFrom, $dateTo) {
	$whereClause = "";
	if (!is_null($dateFrom) && !is_null($dateTo)) {
		$whereClause .= " AND m.CallerTime BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "'";
	}

	return $whereClause;
}

function getCallConnectionTimesBySignals($lat1, $lon1, $lat2, $lon2, $dateFrom, $dateTo, $number) {
	$query = "";
	$query .= "SELECT c.callersignal, ";
	$query .= "       c.receiversignal, ";
	$query .= "       Avg(Time_to_sec(c.connectiontime)) as ConnectionTime, ";
	$query .= "       Count(*) AS DataCount ";
	$query .= "FROM   matched_calls c ";
	$query .= "WHERE  c.callersignal <> 99 ";
	$query .= "       AND c.receiversignal <> 99 ";
	$query .= "       AND c.callersignal <> 0 ";
	$query .= "       AND c.receiversignal <> 0 ";

	$query .= getDateBasedWhereClause($dateFrom, $dateTo);
	$query .= getPositionBasedWhereClause($lat1, $lon1, $lat2, $lon2);

	$query .= " GROUP  BY c.receiversignal, ";
	$query .= "           c.callersignal " ;

	$data = queryDatabase($query);
	$dataByCallerSignal = __($data)->groupBy(function($row) {
		return $row['ReceiverSignal'];
	});
	foreach ($dataByCallerSignal as $key => $value) {
		$dataByCallerSignal[$key] = __($value)->groupBy(function($row) {
			return $row['CallerSignal'];
		});
	}

	return $dataByCallerSignal;
}

function getCallConnectionTimesByDayAndHour($lat1, $lon1, $lat2, $lon2, $dateFrom, $dateTo, $number) {
	$query = "";
	$query .= "SELECT Date_format(c.callertime, '%W') as WeekDay, ";
	$query .= "       Date_format(c.callertime, '%H') as Hour, ";
	$query .= "       Avg(Time_to_sec(c.connectiontime)) AS ConnectionTime, ";
	$query .= "       Count(*)                           AS DataCount ";
	$query .= "FROM   matched_calls c ";
	$query .= "WHERE  c.callersignal <> 99 ";
	$query .= "       AND c.receiversignal <> 99 ";
	$query .= "       AND c.callersignal <> 0 ";
	$query .= "       AND c.receiversignal <> 0 ";

	$query .= getDateBasedWhereClause($dateFrom, $dateTo);
	$query .= getPositionBasedWhereClause($lat1, $lon1, $lat2, $lon2);

	$query .= " GROUP  BY Date_format(c.callertime, '%W'), ";
	$query .= "          Date_format(c.callertime, '%H') ";
	$query .= " ORDER  BY Date_format(c.callertime, '%w'), Date_format(c.callertime, '%H')" ;

	$data = queryDatabase($query);
	$groupedData = __($data)->groupBy(function($row) {
		return $row['WeekDay'];
	});
	foreach ($groupedData as $key => $value) {
		$groupedData[$key] = __($value)->groupBy(function($row) {
			return $row['Hour'];
		});
	}

	return $groupedData;
}

function queryDatabase($query) {
	$response = Array();
	$con = connectDB();
	
	if ($result = $con -> query($query)) {
		while ($item = $result -> fetch_assoc()) {
			$response[] = encodeArrayToUtf($item);
		}
	}
	disconnectDB($con);
	return $response;
}

function getSMS($dateFrom, $dateTo, $number) {
	$sql = "SELECT * FROM sms ";
	$whereClause = " WHERE neighborhood_id IS NOT NULL ";

	$whereClause .= getDateBasedWhereClause($dateFrom, $dateTo);
	
	if(!is_null($number))
		$whereClause .= " AND sourceNumber = '" . $number . "'";

	$sql .= $whereClause;
	return queryDatabase($sql);
}

function getInternetTests($dateFrom, $dateTo, $number) {
	$sql = "SELECT * FROM internet ";
	$whereClause = " WHERE neighborhood_id IS NOT NULL";

	$whereClause .= getDateBasedWhereClause($dateFrom, $dateTo);
	
	if(!is_null($number)) {
		$whereClause .= " AND sourceNumber = '" . $number . "'";
	}

	$sql .= $whereClause;

	return queryDatabase($sql);
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

function getPercentagesOfFailedInternet() {
	$response = array();
	$con = connectDB();
	$sql = "SELECT i1.neighborhood_id AS neighborhood_id, COUNT(case when i1.downloadTime = 0 then 1 else null end) / COUNT(i1.downloadTime) as failed_downloads_percentage, AVG(i1.currentSignal) as signal_average, COUNT(i1.id) as total_samples, n.name as neighborhood_name" 
		. " FROM internet i1" 
		. " JOIN neighborhood n ON n.id = i1.neighborhood_id"
		. " WHERE neighborhood_id is not null"
		. " GROUP BY i1.neighborhood_id";
	$result = $con -> query($sql);
	if ($result) {
		while ($item = $result -> fetch_assoc()) {
			$response[] = encodeArrayToUtf($item);
		}
	}

	disconnectDB($con);
	return $response;
}

function getConnectionTimesPerCompany($lat1, $lon1, $lat2, $lon2, $dateFrom, $dateTo, $number) {
	$query = "";
	$query .= "SELECT CASE ";
	$query .= "         WHEN Lower(m.calleroperatorname) LIKE '%claro%' THEN 'Claro' ";
	$query .= "         WHEN Lower(m.calleroperatorname) LIKE '%personal%' THEN 'Personal' ";
	$query .= "         WHEN Lower(m.calleroperatorname) LIKE '%movistar%' THEN 'Movistar' ";
	$query .= "         ELSE m.calleroperatorname ";
	$query .= "       end                                AS Company, ";
	$query .= "       Avg(Time_to_sec(m.connectiontime)) AS ConnectionTime, ";
	$query .= "       Count(*)                           AS DataCount ";
	$query .= "FROM   matched_calls m ";
	$query .= "WHERE 1=1 ";
	$query .= getDateBasedWhereClause($dateFrom, $dateTo);
	$query .= getPositionBasedWhereClause($lat1, $lon1, $lat2, $lon2);

	$query .= "GROUP BY CASE ";
	$query .= "            WHEN Lower(m.calleroperatorname) LIKE '%claro%' THEN 'Claro' ";
	$query .= "            WHEN Lower(m.calleroperatorname) LIKE '%personal%' THEN 'Personal' ";
	$query .= "            WHEN Lower(m.calleroperatorname) LIKE '%movistar%' THEN 'Movistar' ";
	$query .= "            ELSE m.calleroperatorname ";
	$query .= "          end " ;

	return queryDatabase($query);
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

