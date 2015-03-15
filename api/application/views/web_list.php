<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<link rel="stylesheet" type="text/css" href="/app/css/bootstrap.css" media="all" />
		<link rel="stylesheet" type="text/css" href="/app/css/reset.css" media="all" />
		<link rel="stylesheet" type="text/css" href="/app/css/form.css" media="all" />

		<script type="text/javascript" src="/app/js/lib/jquery-1.9.1.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				$('a.info').on('click', function() {
					$('.more-instructions').hide();
					$('p.more-info').toggle();
				});
				$('a.instructions').on('click', function() {
					$('p.more-info').hide();
					$('.more-instructions').toggle();
				});
			});
		</script>
	</head>
	<body>
		<div id="wrapper">
			<div id="header">
				<center>
					<h2>Belastning Malmö</h2>
				</center>
			</div>
			<div id="content">
				<br />

				<a href="#" class="info"><button class="btn btn-primary">Mer info</button></a>
				<a href="#" class="instructions"><button class="btn btn-primary">Instruktioner</button></a>
				<p class="more-info">
					Nedan finns en lista med de tidpunkter som är aktuella de närmaste timmarna i Malmö. Klicka på "Svara" för att fylla i formuläret. Formuläret bör fyllas i inom en timme från den angivna tiden. Då är raden grön. Man kan komplettera i efterhand om man inte hann fylla i vid den angivna tiden men svaren skall då vara för den angivna tiden.
				</p>
				<ul class="more-instructions">
					<li>Klicka på Svara på den tiden som är aktuell ur listan av tider (den kommer vara grönmarkerad och tydlig)</li>
					<li>Svara på de tre korta frågorna i formuläret och tryck på Skicka</li>
				</ul>
				<hr />
				<?php foreach($readings as $key => $reading): ?>
				<div class="reading <?php echo $status[$reading['code']]; ?>">
					<div class="left">
						<div class="header"><?php echo $reading['day'].' '.$reading['hour']; ?></div>
						<div class="status <?php echo $status[$reading['code']]; ?>"><?php echo $reading['status']; ?></div>
					</div>
					<div class="right">
					<?php if ($reading['code'] == 1 || $reading['status'] !== 'Klar' && $reading['code'] == 2): ?>
						<a href="<?php echo site_url('welcome/reading/'.$reading['id']);?>" class="btn">Svara</a>
					<?php endif; ?>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
			<div id="footer">

			</div>
		</div>
	</body>
</html>