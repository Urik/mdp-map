<?php
require 'Slim/Slim.php';
include_once "DB/MapDB.php";
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim( array('debug' => true, 'templates.path' => './'));
//$app->response->headers->set('Content-Type', 'application/json');

$app->get('/', function() use ($app) {
	$app->render('/index_template.php');
});

$app->get('/charts', function() use ($app) {
	$app->render('/index_charts.php');
});

$app->group('/api', function() use ($app) {

	$app->get('/internet', function() use ($app) {
		$params = getQueryParameters($app);
		echo json_encode(getInternetTests($params->dateFrom, $params->dateTo, $params->number));
	});

	$app->get('/neighborhoods', function() use ($app) {
		echo json_encode(getNeighborhoods());
	});

	$app->get('/zones', function() use ($app) {
		echo json_encode(getZones());
	});

	$app->group('/calls', function() use($app) {
		$app->get('/', function() use ($app) {
			$params = getQueryParameters($app);
			echo json_encode(getCalls($params->lat1, $params->lon1, $params->lat2, $params->lon2, $params->dateFrom, $params->dateTo, $params->number));
		});

		$app->get('/avgconnectiontime', function() use ($app) {
			$params = getQueryParameters($app);
			echo json_encode(getAVGTime('call', $params->dateFrom, $params->dateTo, $params->number));
		});

		$app->get('/avgcalltimepersignals', function() use($app) {
			$params = getQueryParameters($app);
			echo json_encode(getCallConnectionTimesBySignals($params->lat1, $params->lon1, $params->lat2, $params->lon2, $params->dateFrom, $params->dateTo, $params->number));
		});

		$app->get('/avgcalltimeperdayandhour', function() use ($app) {
			$params = getQueryParameters($app);
			echo json_encode(getCallConnectionTimesByDayAndHour($params->lat1, $params->lon1, $params->lat2, $params->lon2, $params->dateFrom, $params->dateTo, $params->number));
		});
	});

	$app->get('/sms', function() use ($app) {
		$params = getQueryParameters($app);
		echo json_encode(getSMS($params->dateFrom, $params->dateTo, $params->number));
	});

	$app->get('/avgtimeDown', function() use ($app) {
		$params = getQueryParameters($app);
		$dateFrom = $app->request()->get('dateFrom');
		$dateTo = $app->request()->get('dateTo');	
		$number = $app->request()->get('number');
		echo json_encode(getAVGTime('internet', $dateFrom, $dateTo, $number));
	});
	$app->get('/avgtimeSMS', function() use ($app) {
		$params = getQueryParameters($app);
		$dateFrom = $app->request()->get('dateFrom');
		$dateTo = $app->request()->get('dateTo');	
		$number = $app->request()->get('number');
		echo json_encode(getAVGTime('SMS', $dateFrom, $dateTo, $number));
	});
	$app->get('/avgSignal', function() use ($app) {
		$params = getQueryParameters($app);
		$dateFrom = $app->request()->get('dateFrom');
		$dateTo = $app->request()->get('dateTo');	
		$number = $app->request()->get('number');
		echo json_encode(getAVGTime('signal', $dateFrom, $dateTo, $number));
	});
	$app->get('/internet/failed/average', function() use ($app) {
		echo json_encode(getPercentagesOfFailedInternet());
	});

});
$app->run();

function getQueryParameters($app) {
	$params = new stdClass;
	$params->dateFrom = $app->request->get('dateFrom');
	$params->dateTo = $app->request->get('dateTo');
	$params->number = $app->request->get('number');
	$params->lat1 = $app->request->get('lat1');
	$params->lon1 = $app->request->get('lon1');
	$params->lat2 = $app->request->get('lat2');
	$params->lon2 = $app->request->get('lon2');
	return $params;
}

function getFunctionWithDateAndPositionParameters($app, $func)  {
	$params = getQueryParameters($app);
	return function() use ($params) {
		return $func($params->lat1, $params->lon1, $params->lat2, $params->lon2, $params->dateFrom, $params->dateTo);
	};
}
?>