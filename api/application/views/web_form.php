<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title></title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<link rel="stylesheet" type="text/css" href="/app/css/bootstrap.css" media="all" />
		<link rel="stylesheet" type="text/css" href="/app/css/reset.css" media="all" />
		<link rel="stylesheet" type="text/css" href="/app/css/form.css" media="all" />
	</head>
	<body>
		<div id="wrapper">
			<div id="header">
				<center>
					<h2>Belastning Malmö</h2>
				</center>
			</div>
			<div id="content">
				<?php if (isset($alert)): ?>
				<div class="alert <?php echo $alert['type']?>">
  					<?php echo $alert['message']; ?>
				</div>
				<?php endif; ?>
				<form name="form" action="" method="post">
					<label for="role">Arbetsroll</label>
					<select name="type">
						<option value="1" <?php echo ($disabled[1] ? 'disabled="disabled"' : '');?>>Läkare<?php echo ($disabled[1] ? ' - ifylld' : '');?></option>
						<option value="2" <?php echo ($disabled[2] ? 'disabled="disabled"' : '');?>>Sjuksköterska <?php echo ($disabled[2] ? ' - ifylld' : '');?></option>
						<option value="3">DAL</option>
						<option value="4">DASK</option>
						<option value="5">Undersköterska</option>
					</select>
					<hr />
					<label for="question_1">Hur bedömer du att arbetsbelastningen på ditt ansvarsområde på akuten varit under den senaste timmen? <br />1: Mycket låg, 6: Mycket hög</label>
					<select name="question_1">
						<option></option>
						<option>1</option>
						<option>2</option>
						<option>3</option>
						<option>4</option>
						<option>5</option>
						<option>6</option>
					</select>
					<hr />
					<label for="question_1">Har du känt dig stressad i din arbete under den senaste timmen? <br /> 1: Inte alls, 6: Mycket stressad</label>
					<select name="question_2">
						<option></option>
						<option>1</option>
						<option>2</option>
						<option>3</option>
						<option>4</option>
						<option>5</option>
						<option>6</option>
					</select>
					<hr />
					<button class="btn btn-success" type="submit">Skicka</button>
					<input type="hidden" name="reading" value="<?php echo $reading['id']?>" />
					<a href="<?php echo site_url(); ?>" class="btn btn-warning">Tillbaka</a>
				</form>
			</div>
			<div id="footer">

			</div>
		</div>
	</body>
</html>