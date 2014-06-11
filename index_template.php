<!DOCTYPE html>

<html>
	<head>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<meta charset="utf-8">
		<title>Polygon Arrays</title>

		<link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.2/css/bootstrap-combined.min.css" rel="stylesheet">
		<link rel="stylesheet" type="text/css" media="screen"
		href="http://tarruda.github.com/bootstrap-datetimepicker/assets/css/bootstrap-datetimepicker.min.css">
		<link rel="stylesheet" type="text/css" media="screen"
		href="css/styles.css">

		<script type="text/javascript" src="./js/jquery-2.1.1.min.js"></script>
		<script type="text/javascript" src="./js/underscore-min.js"></script>
		<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=geometry&sensor=false"></script>
		<script src="js/markerwithlabel.js" type="text/javascript"></script>
		<script type="text/javascript" src="./js/index.js"></script>

	</head>
	<body>
		<div>
			<a id="calls_button" href="#">Ver llamadas</a>
			<a id="internet_button" href="#">Ver Internet</a>
			<a id="sms_button" href="#">Ver SMS</a>
			<a id="avgTime_button" href="#">Tiempo de con. por Zonas</a>
			<a id="avgDownloadTime_button" href="#">Tiempo de Descarga por Zonas</a>
			<a id="avgSMSTime_button" href="#">Tiempo de Env. SMS por Zonas</a>

		</div>
		<div id="calendarWrapper">
			<div id="dateFrom" class="input-append date">
				<input id='inputDateFrom' type="text" placeholder="Fecha Desde">
				</input>
				<span class="add-on"> <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i> </span>
			</div>
			<div id="dateTo" class="input-append date">
				<input id='inputDateTo' type="text" placeholder="Fecha Hasta">
				</input>
				<span class="add-on"> <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i> </span>
			</div>
		</div>
		<div id="map-canvas"></div>
		<div id="neighTable">
			<p>
				Nada que mostrar
			</p>
		</div>

	</body>
	<script type="text/javascript"
	src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.2/js/bootstrap.min.js"></script>
	<script type="text/javascript"
	src="http://tarruda.github.com/bootstrap-datetimepicker/assets/js/bootstrap-datetimepicker.min.js"></script>
	<script type="text/javascript"
	src="http://tarruda.github.com/bootstrap-datetimepicker/assets/js/bootstrap-datetimepicker.pt-BR.js"></script>
	<script type="text/javascript">
		$('#dateFrom').datetimepicker({
			format : 'yyyy-MM-dd hh:mm:ss',
			language : 'en'
		});
		$('#dateTo').datetimepicker({
			format : 'yyyy-MM-dd hh:mm:ss',
			language : 'en'
		});
	</script>
</html>