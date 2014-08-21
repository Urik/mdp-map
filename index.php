<?php
require 'Slim/Slim.php';
include_once "DB/MapDB.php";
include_once 'underscore.php';
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

	$app->group('/neighborhoods', function() use($app) {
		$app->get('/', function() use($app) {
			echo json_encode(getNeighborhoods());
		});

		$app->get('/averagesignals', function() use($app) {
			$func = getFunctionWithDateAndPositionParameters($app, 'getAverageSignalPerNeighborhood');
			echo json_encode($func());
		});
	});

	$app->get('/zones', function() use ($app) {
		echo json_encode(getZones());
	});

	$app->group('/calls', function() use($app) {
		$app->get('/', function() use ($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getCalls');
			echo json_encode($queryFunc());
		});

		$app->get('/failed', function() use ($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getFailedCalls');
			echo json_encode($queryFunc());
		});

		$app->get('/avgconnectiontime', function() use ($app) {
			$params = getQueryParameters($app);
			echo json_encode(getAVGTime('call', $params->dateFrom, $params->dateTo, $params->number));
		});

		$app->get('/avgcalltimepersignals', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getCallConnectionTimesBySignals');
			echo json_encode($queryFunc());
		});

		$app->get('/avgcalltimeperdayandhour', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getCallConnectionTimesByDayAndHour');
			echo json_encode($queryFunc());
		});

		$app->get('/avgcalltimeperoperator', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getConnectionTimesPerCompany');
			echo json_encode($queryFunc());
		});

		$app->get('/scatteredsignalconnectiontimedata', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getCalls');
			$data = $queryFunc();
			$pluckedData = __($data)->map(function($row) {
				return array(
					'operator' => $row['caller_operator_name'],
					'signal' => $row['caller_signal'],
					'connectionTime' => substr($row['connection_time'], 6)	//We only want the seconds!
					);
			});
			$filteredData = __($pluckedData)->reject(function($data) { return $data['signal'] == "99"; });
			$groupedData = __($filteredData)->groupBy(function($row) {
				return $row['operator'];
			});

			echo json_encode($groupedData);
		});
	});

	$app->group('/internet', function() use($app) {
		$app->get('/downloadtimeperhour', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getDownloadTimesPerHour');
			echo json_encode($queryFunc());
		});

		$app->get('/downloadtimeperoperator', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getDownloadTimesPerOperator');
			echo json_encode($queryFunc());
		});

		$app->get('/failedproportionperneighborhood', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getFailedDownloadsProportionPerNeighborhood');
			echo json_encode($queryFunc());
		});

		$app->get('/failedproportionperoperator', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getFailedDownloadsProportionPerOperator');
			echo json_encode($queryFunc());
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
	return function() use ($params, $func) {
		return call_user_func($func, $params->lat1, $params->lon1, $params->lat2, $params->lon2, $params->dateFrom, $params->dateTo, null);
	};
}
?>