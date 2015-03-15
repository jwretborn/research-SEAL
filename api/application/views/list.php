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
			<div id="header">
				<p>
					Belastningsskattning akuten
				<p>
				<center>
					<h1><?php echo $hospital; ?></h1>
				</center>
			</div>
			<div id="readings">
				<table>
					<thead>
						<th></th>
						<th>Kod</th>
						<th>Tid</th>
						<th>Status</th>
					</thead>
					<tbody>
					<?php foreach($readings as $key => $r): ?>
						<tr>
							<td class="date"><?php echo strtr((date('H:i', $r[count($r)-1]['timestamp']) == '00:00') ? date('l j/n', $r[0]['timestamp']-1) : date('l j/n', $r[0]['timestamp']), $swe_days); ?></td>
							<td colspan="3" class="date"></td>
						</tr>
						<?php foreach($r as $index => $reading): ?>
						<tr>
							<td></td>
							<td><?php echo $reading['id']; ?></td>
							<td><?php echo (date('H:i', $reading['timestamp']) == '00:00') ? '24:00' : date('H:i', $reading['timestamp']); ?></td>
							<td><input type="checkbox" /></td>
						</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</body>
</html>