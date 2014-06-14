<?php
require 'Slim/Slim.php';
include_once "DB/MapDB.php";
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim( array('debug' => true, 'templates.path' => './'));
//$app->response->headers->set('Content-Type', 'application/json');

$app -> get('/', function() use ($app) {
	$app -> render('/index_template.php');
});

$app -> get('/internet', function() use ($app) {
	$dateFrom = $app -> request() -> get('dateFrom');
	$dateTo = $app -> request() -> get('dateTo');
	echo json_encode(getInternetTests($dateFrom, $dateTo));
});

$app -> get('/neighborhoods', function() use ($app) {
	echo json_encode(getNeighborhoods());
});

$app -> get('/zones', function() use ($app) {
	echo json_encode(getZones());
});

$app -> get('/calls', function() use ($app) {
	$lat1 = $app -> request() -> get('lat1');
	$lon1 = $app -> request() -> get('lon1');
	$lat2 = $app -> request() -> get('lat2');
	$lon2 = $app -> request() -> get('lon2');
	$dateFrom = $app -> request() -> get('dateFrom');
	$dateTo = $app -> request() -> get('dateTo');
	echo json_encode(getCalls($lat1, $lon1, $lat2, $lon2, $dateFrom, $dateTo));
});

$app -> get('/sms', function() use ($app) {
	$dateFrom = $app -> request() -> get('dateFrom');
	$dateTo = $app -> request() -> get('dateTo');
	echo json_encode(getSMS($dateFrom, $dateTo));
});

$app -> get('/avgtime', function() use ($app) {
	$dateFrom = $app -> request() -> get('dateFrom');
	$dateTo = $app -> request() -> get('dateTo');
	echo json_encode(getAVGTime('call', $dateFrom, $dateTo));
});
$app -> get('/avgtimeDown', function() use ($app) {
	$dateFrom = $app -> request() -> get('dateFrom');
	$dateTo = $app -> request() -> get('dateTo');	
	echo json_encode(getAVGTime('internet', $dateFrom, $dateTo));
});
$app -> get('/avgtimeSMS', function() use ($app) {
	$dateFrom = $app -> request() -> get('dateFrom');
	$dateTo = $app -> request() -> get('dateTo');	
	echo json_encode(getAVGTime('SMS', $dateFrom, $dateTo));
});
$app -> get('/avgSignal', function() use ($app) {
	$dateFrom = $app -> request() -> get('dateFrom');
	$dateTo = $app -> request() -> get('dateTo');	
	echo json_encode(getAVGTime('signal', $dateFrom, $dateTo));
});

$app -> run();
?>