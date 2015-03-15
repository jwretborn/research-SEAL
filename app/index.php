<!DOCTYPE html>
<html lang="sv" ng-app="seal" id="ng-app" xmlns:ng="http://angularjs.org">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />
		<meta name="fragment" content="!" />
		
		<link rel="stylesheet" type="text/css" href="css/bootstrap.css" media="all" />
		<link rel="stylesheet" type="text/css" href="css/jquery-ui.css" media="all" />
		<link rel="stylesheet" type="text/css" href="css/style.css" media="all" />

		<script type="text/javascript" src="js/lib/jquery-1.9.1.js"></script>
		<script type="text/javascript" src="js/lib/jquery-ui.js"></script>		
		<script type="text/javascript" src="js/lib/angular-1.0.1.js"></script>
		<script type="text/javascript" src="js/lib/angular-resource-1.0.1.js"></script>
		<script type="text/javascript" src="js/lib/underscore.js"></script>
		<script type="text/javascript" src="js/lib/highcharts.js"></script>
		<script type="text/javascript" src="js/lib/highcharts.export.js"></script>

		<script type="text/javascript" src="js/app.js"></script>

		<script type="text/javascript" src="js/controllers/start.js"></script>
		<script type="text/javascript" src="js/controllers/hospitals.js"></script>
		<script type="text/javascript" src="js/controllers/readings.js"></script>
		<script type="text/javascript" src="js/controllers/statistics.js"></script>
		<script type="text/javascript" src="js/controllers/statistics/default.js"></script>
		<script type="text/javascript" src="js/controllers/statistics/times.js"></script>
		<script type="text/javascript" src="js/controllers/statistics/days.js"></script>
		<script type="text/javascript" src="js/controllers/statistics/assessment.js"></script>
		<script type="text/javascript" src="js/controllers/statistics/variable.js"></script>

		<script type="text/javascript" src="js/services/app.js"></script>
		<script type="text/javascript" src="js/services/localStorage.js"></script>
		<script type="text/javascript" src="js/services/hospitals.js"></script>
		<script type="text/javascript" src="js/services/readings.js"></script>
		<script type="text/javascript" src="js/services/times.js"></script>
		<script type="text/javascript" src="js/services/variables.js"></script>

		<script type="text/javascript" src="js/directives/default-chart.js"></script>
		<script type="text/javascript" src="js/directives/time-chart.js"></script>
		<script type="text/javascript" src="js/directives/time-assessment-chart.js"></script>
		<script type="text/javascript" src="js/directives/day-chart.js"></script>
		<script type="text/javascript" src="js/directives/as-default-chart.js"></script>
		<script type="text/javascript" src="js/directives/variable-chart.js"></script>

		<title>SEAL</title>

		<!--[if lte IE 8]>
    		<script>
    			document.createElement('ng-include');
    			document.createElement('ng-pluralize');
    			document.createElement('ng-view');
 			
    			// Optionally these for CSS
    			document.createElement('ng:include');
    			document.createElement('ng:pluralize');
    			document.createElement('ng:view');
    		</script>
    	<![endif]-->
    	<script type="text/javascript">
			document.createElement('ng-view');
			document.createElement('editable-input');
		</script>
	</head>
	<body>
		<div id="wrapper">
			<div id="messages"></div>
			<div id="header">
				<h1>SEAL</h1>
				<h3>Skåne Emergency department Assessment of patient Load</h3>
			</div>
			<div id="menu" class="navbar">
				<div class="navbar-inner">
					<a class="brand"></a>
					<ul class="nav">
						<li data-name="start"><a href="#/start">Start</a></li>
						<li data-name="register"><a href="#/hospitals">Sjukhus</a></li>
						<li data-name="priser"><a href="#/readings">Mätpunkter</a></li>
						<li data-name="statistik"><a href="#/statistics">Statistik</a></li>
					</ul>
				</div>
			</div>
			
			<div ng-view></div>
			
			<div id="footer">
				<div class="footer-text"></div>
			</div>
		</div>
    </body>
</html>
