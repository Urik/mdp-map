<?php

include_once 'DB/DBConnection.php';

function getNeighborhoods(){
    $con = connectDB();
  	if ($result = $con->query("SELECT n.name, c.latitude, c.longitude FROM neighborhood AS n INNER JOIN coordinate AS c ON n.id = c.neighborhood_id"))
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

function renderNeighborhood($i){
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
			
	
			
}

function getCalls(){
	$con = connectDB();
  	if ($result = $con->query("SELECT * FROM tesis.`call`"))
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
?>

