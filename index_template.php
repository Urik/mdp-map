<!DOCTYPE html>

<html>
	<head>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<meta charset="utf-8">
		<title>Polygon Arrays</title>

		<link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap-datetimepicker.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="css/styles.css">

		<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>

		<script type="text/javascript" src="./js/underscore-min.js"></script>
		<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=geometry,drawing&sensor=false"></script>
		<script type="text/javascript" src="./js/index.js"></script>
		<script type="text/javascript" src="js/moment.js"></script>
		<script type="text/javascript" src="js/bootstrap.min.js"></script>
		<script type="text/javascript" src="js/bootstrap-datetimepicker.js"></script>
		<script type="text/javascript" src="js/locales/bootstrap-datetimepicker.es.js"></script>
		<script type="text/javascript" src="js/highcharts.js"></script>

	</head>
	<body>

		<nav class="navbar navbar-default" role="navigation">
			<div class="container-fluid">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="#">Mar del Celular</a>
				</div>

				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
					<ul class="nav navbar-nav">
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">Llamadas <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li>
									<a id="calls_button" href="#">Ver todas</a>
								</li>
								<li>
									<a id="avgTime_button" href="#">Ver por zona</a>
								</li>
							</ul>
						</li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">Datos de Internet <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li>
									<a id="internet_button" href="#">Ver todas</a>
								</li>
								<li>
									<a id="avgDownloadTime_button" href="#">Ver por zona</a>
								</li>
							</ul>
						</li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">SMS <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li>
									<a id="sms_button" href="#">Ver todas</a>
								</li>
								<li>
									<a id="avgSMSTime_button"  href="#">Ver por zona</a>
								</li>
							</ul>
						</li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">Señal <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li>
									<a id="avgSignal_button"  href="#">Ver por zona</a>
								</li>
							</ul>
						</li>

					</ul>
					<form class="navbar-form navbar-left" role="search">

						<div class="row">
							<div class='col-sm-6'>
								<div class="form-group">
									<div class='input-group date' id='dateFrom'>
										<input type='text' id="inputDateFrom" class="form-control" placeholder="Fecha de Inicio" readonly="true"/>
										<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span> </span>
									</div>
								</div>
							</div>
							<div class='col-sm-6'>
								<div class="form-group">
									<div class='input-group date' id='dateTo'>
										<input type='text' id="inputDateTo" class="form-control" placeholder="Fecha de Final" readonly="true" />
										<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span> </span>
									</div>
								</div>

							</div>
						</div>
					</form>
					<button type="button" id="reload" class="btn btn-default navbar-btn">
						<span class="glyphicon glyphicon-refresh"></span> Recargar
					</button>
					<button type="button" id="clearDates" class="btn btn-default navbar-btn">
						Limpiar Fechas
					</button>

					<ul class="nav navbar-nav navbar-right">
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">Filtros <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li>
									<a id="filterModalNumber"  data-toggle="modal" data-target="#myModal" href="#">Añadir Número de Teléfono</a>
								</li>
								<li class="divider"></li>
								<li>
									<a id="clearFilters" href="#">Borrar todos</a>
								</li>
							</ul>
						</li>
					</ul>
				</div><!-- /.navbar-collapse -->
			</div><!-- /.container-fluid -->
		</nav>

		<div id="map-canvas"></div>
		<div id="neighTable">
			<p>
				Nada que mostrar
			</p>
		</div>
		<div id="chart" style="width: 100%; height: 400px;"></div>
	<!--Filter Number Modal -->
	<div class="modal fade" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Añadir Número de Teléfono</h4>
      </div>
      <div class="modal-body">
        <input id="filterNumber" type="text" class="form-control" placeholder="Número de Teléfono a filtrar">
      </div>
      <div class="modal-footer">
        <button id="numberFilterCancel" type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button id="useFilterButton" type="button" class="btn btn-primary" data-dismiss="modal">Usar Filtro</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

	
	</body>

</html>