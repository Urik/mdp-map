<!DOCTYPE html>

<html>
	<head>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<meta charset="utf-8">
		<title>Polygon Arrays</title>
		<style>
			html, body, #map-canvas {
				height: 100%;
				margin: 0px;
				padding: 0px
			}
		</style>
		<script type="text/javascript" src="./js/jquery-2.1.1.min.js"></script>
		<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=geometry&sensor=false"></script>
		<script type="text/javascript" src="./js/index.js"></script>
		<script src="js/markerwithlabel.js" type="text/javascript"></script>
	</head>
	<body>
		<div>
			<a href="index.php?view=1">Ver llamadas</a>
			<a href="index.php?view=2">Ver Internet</a>
			<a href="index.php?view=3">Ver SMS</a>
			<!--<a href="index.php?view=4">Internet por Zonas</a>-->
		</div>
		<div id="map-canvas"></div>
		<div id="lenght"></div>

	</body>
</html>