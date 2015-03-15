<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title></title>

		<link rel="stylesheet" type="text/css" href="/app/css/reset.css" media="all" />
		<link rel="stylesheet" type="text/css" href="/app/css/print.css" media="all" />
	</head>
	<body>
		<div id="wrapper">
		<?php foreach ($readings as $key => $hospital): ?>
			<?php foreach ($hospital as $index => $time): ?>
				<?php foreach ($time as $i => $reading): ?>
					<?php foreach ($reading['forms'] as $key => $form): ?>
			<div id="header">
				<span class="code">Kod: <?php echo $reading['id']; ?></span>
				<p>
					Belastningsskattning akuten: <?php echo $reading['hospital']; ?>
					<br />
					Formulär: <?php echo $form['id']; ?>
				<p>

			</div>
			<div id="time">
				<center>
					<p>
						<?php echo $reading['day'];?> - <?php echo $reading['hour']; ?><br />
						<span style="font-size:20pt;">
							<?php echo ($form['type'] === '1') ? 'Läkare' : 'Sjuksköterska'; ?>
						</span>
					</p>
				</center>
			</div>
			<div class="questions">
				<div class="question">
					<div class="q">
						1. Hur bedömer du att arbetsbelastningen på hela akuten varit under den senaste timmen?
					</div>
					<div class="a">
						<span class="startpoint">Mycket låg</span>
						<span class="checkbox">1</span>
						<span class="checkbox">2</span>
						<span class="checkbox">3</span>
						<span class="checkbox">4</span>
						<span class="checkbox">5</span>
						<span class="checkbox">6</span>
						<span class="endpoint">Mycket hög</span>
					</div>
				</div>
				<div class="question">
					<div class="q">
						2. Har du känt dig stressad under den senaste timmen?
					</div>
					<div class="a">
						<span class="startpoint">Inte alls</span>
						<span class="checkbox">1</span>
						<span class="checkbox">2</span>
						<span class="checkbox">3</span>
						<span class="checkbox">4</span>
						<span class="checkbox">5</span>
						<span class="checkbox">6</span>
						<span class="endpoint">Mycket stressad</span>
					</div>
				</div>
				<span class="code">Kod: <?php echo $reading['id']; ?></span>
			</div>
			<br />
			<div class="pb"></div>
					<?php endforeach; ?>
				<?php endforeach; ?>
			<?php endforeach; ?>
		<?php endforeach; ?>
		</div>
	</body>
</html>