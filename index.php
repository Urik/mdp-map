<?php
require 'Slim/Slim.php';
include_once "DB/MapDB.php";
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(array(
	'debug' => true,
	'templates.path' => './'
));
//$app->response->headers->set('Content-Type', 'application/json');

$app->get('/', function() use($app) {
	$app->render('/index_template.php');
});

$app->get('/internet', function() use($app) {
	echo json_encode(getInternetTests());
});

$app->get('/neighborhoods', function() use($app) {
	echo json_encode(getNeighborhoods());
});

$app->get('/zones', function() use($app) {
	echo json_encode(getZones());
});

$app->get('/calls', function() use($app) {
	$lat1 = $app->request()->get('lat1');
	$lon1 = $app->request()->get('lon1');
	$lat2 = $app->request()->get('lat2');
	$lon2 = $app->request()->get('lon2');
	echo json_encode(getCalls($lat1, $lon1, $lat2, $lon2));
});

$app->get('/sms', function() use($app) {
	echo json_encode(getSMS());
});

$app->get('/avgtime', function() use($app) {
	echo json_encode(getAVGTime('call'));
});
$app->get('/avgtimeDown', function() use($app) {
	echo json_encode(getAVGTime('internet'));
});
$app->get('/avgtimeSMS', function() use($app) {
	echo json_encode(getAVGTime('SMS'));
});

$app->run();
?>