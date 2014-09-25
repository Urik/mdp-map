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

	$app->get('/signalsperoperator', function() use($app) {
		$func = getFunctionWithDateAndPositionParameters($app, 'getAverageSignalPerOperator');
		echo json_encode($func());
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

		$app->get('/avgtotalconnectiontime', function() use ($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getAverageConnectionTime');
			echo json_encode($queryFunc());
		});

		$app->get('/avgconnectiontime', function() use ($app) {
			$params = getQueryParameters($app);
			echo json_encode(getAVGTime('call', $params->dateFrom, $params->dateTo, $params->operator, $params->number));
		});

		$app->get('/avgcalltimepersignals', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getCallConnectionTimesBySignals');
			echo json_encode($queryFunc());
		});

		$app->get('/avgcalltimeperdayandhour', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getCallConnectionTimesByDayAndHour');
			echo json_encode($queryFunc());
		});

		$app->get('/avgcalltimeperbattery', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getAverageConnectionTimePerBatteryLevel');
			echo json_encode($queryFunc());
		});

		$app->get('/avgcalltimeperoperator', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getConnectionTimesPerCompany');
			echo json_encode($queryFunc());
		});

		$app->get('/avgcalltimeperneighborhood', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getAverageConnectionTimePerNeighborhood');
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
			$groupedData = produceRandomSample($groupedData, 100);

			echo json_encode($groupedData);
		});

		$app->get('/failurerateperoperator', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getFailedConnectionsRatePerOperator');
			echo json_encode($queryFunc());
		});

		$app->get('/failurerateperneighborhood', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getFailedConnectionsRatePerNeighborhood');
			echo json_encode($queryFunc());
		});
	});

	
	$app->group('/internet', function() use($app) {

		$app->get('/', function() use ($app) {
			$func = getFunctionWithDateAndPositionParameters($app, 'getInternetTests');
			echo json_encode($func());
		});

		$app->get('/downloadtime', function() use($app) {
			$func = getFunctionWithDateAndPositionParameters($app, 'getAverageDownloadTime');
			echo json_encode($func());
		});

		$app->get('/downloadtimeperhour', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getDownloadTimesPerHour');
			echo json_encode($queryFunc());
		});

		$app->get('/downloadtimeperoperator', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getDownloadTimesPerOperator');
			echo json_encode($queryFunc());
		});

		$app->get('/downloadtimepersignal', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getAverageDownloadTimePerSignal');
			echo json_encode($queryFunc());
		});

		$app->get('/downloadtimeperneighborhood', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getAverageDownloadTimePerNeighborhood');
			echo json_encode($queryFunc());
		});

		$app->get('/downloadtimeperbatterylevel', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getAverageDownloadTimePerBatteryLevel');
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

	$app->group('/sms', function() use ($app) {
		$app->get('/', function() use ($app) {
			$params = getQueryParameters($app);
			echo json_encode(getSMS($params->dateFrom, $params->dateTo, $params->neighborhoodId, $params->operator, $params->number));
		});

		$app->get('/sendingtimeperoperator', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getSmsSendinTimePerOperator');
			echo json_encode($queryFunc());
		});

		$app->get('/failurerateperoperator', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getFailedSmsProportionPerOperator');
			echo json_encode($queryFunc());
		});

		$app->get('/sendingtimepersignal', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getSmsSendingTimePerSignal');
			echo json_encode($queryFunc());
		});
		
		$app->get('/sendingtimeperbattery', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getSmsSendingTimePerBatteryLevel');
			echo json_encode($queryFunc());
		});
		
		$app->get('/sendingtimeperneighborhood', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getSmsSendingTimePerNeighborhood');
			echo json_encode($queryFunc());
		});
		
		$app->get('/failurerateperneighborhood', function() use($app) {
			$queryFunc = getFunctionWithDateAndPositionParameters($app, 'getFailedSmsProportionsPerNeighborhood');
			echo json_encode($queryFunc());
		});
	});

	$app->get('/avgtimeDown', function() use ($app) {
		$params = getQueryParameters($app);
		$dateFrom = $app->request()->get('dateFrom');
		$dateTo = $app->request()->get('dateTo');	
		$number = $app->request()->get('number');
		echo json_encode(getAVGTime('internet', $dateFrom, $dateTo, $params->operator, $number));
	});
	$app->get('/avgtimeSMS', function() use ($app) {
		$params = getQueryParameters($app);
		$dateFrom = $app->request()->get('dateFrom');
		$dateTo = $app->request()->get('dateTo');	
		$number = $app->request()->get('number');
		echo json_encode(getAVGTime('SMS', $dateFrom, $dateTo, $params->operator, $number));
	});
	$app->get('/avgSignal', function() use ($app) {
		$params = getQueryParameters($app);
		$dateFrom = $app->request()->get('dateFrom');
		$dateTo = $app->request()->get('dateTo');	
		$number = $app->request()->get('number');
		echo json_encode(getAVGTime('signal', $dateFrom, $dateTo, $params->operator, $number));
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
	$params->operator = $app->request->get('operator');
	$params->neighborhoodId = $app->request->get('neighborhoodid');
	return $params;
}

function getFunctionWithDateAndPositionParameters($app, $func)  {
	$params = getQueryParameters($app);
	return function() use ($params, $func) {
		return call_user_func($func, $params->lat1, $params->lon1, $params->lat2, $params->lon2, $params->dateFrom, $params->dateTo,  $params->neighborhoodId, $params->operator, $params->number);
	};
}

function produceRandomSample($array, $maxSize) {
	foreach ($array as $key => $value) {
		$randomKeys = array_rand($value, sizeof($value) >= $maxSize ? $maxSize : sizeof($value));
		$array[$key] = __($randomKeys)->map(function($randomKey) use($value) {
			return $value[$randomKey];
		});
	}

	return $array;
}
?>