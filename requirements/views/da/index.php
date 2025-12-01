<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="no" lang="no">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="en"/>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>Yii - Tjek systemkrav</title>
</head>

<body>
<div id="page">

<div id="header">
<h1>Yii - Tjek systemkrav</h1>
</div><!-- header-->

<div id="content">
<h2>Beskrivelse</h2>
<p>
Dette script tjekker om serverkonfigurationen opfylder kravene for at køre
<a href="https://www.yiiframework.com/">Yii</a>-applikationer.
Det tjekker om serveren kører korrekt version af PHP, om nødvendige extensions
er indlæst og om PHP-indstillingerne i php.ini er korrekte.
</p>

<h2>Konklusion</h2>
<p>
<?php if($result>0): ?>
Tillykke! Konfigurationen på serveren tilfredsstiller alle krav for at køre Yii.
<?php elseif($result<0): ?>
Konfigurationen på serveren tilfredsstiller minimumskravene til Yii. Vær opmærksom
på advarslerne listet nedenfor, i tilfælde af at din applikation anvender nogen af
funktionerne.
<?php else: ?>
Desværre tilfredsstiller konfigurationen af serveren ikke minimumskravene til Yii.
<?php endif; ?>
</p>

<h2>Detaljer</h2>

<table class="result">
<tr><th>Navn</th><th>Resultat</th><th>Kræves af</th><th>Notat</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
	<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'ok' : ($requirement[1] ? 'fejl' : 'advarsel'); ?>">
	<?php echo $requirement[2] ? 'Ok' : 'Fejl'; ?>
	</td>
	<td>
	<?php echo $requirement[3]; ?>
	</td>
	<td>
	<?php echo $requirement[4]; ?>
	</td>
</tr>
<?php endforeach; ?>
</table>

<table>
<tr>
<td class="passed">&nbsp;</td><td>ok</td>
<td class="failed">&nbsp;</td><td>fejl</td>
<td class="warning">&nbsp;</td><td>advarsel</td>
</tr>
</table>

</div><!-- content -->

<div id="footer">
<?php echo $serverInfo; ?>
</div><!-- footer -->

</div><!-- page -->
</body>
</html>