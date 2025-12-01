<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="no" lang="no">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="en"/>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>Yii - Sjekk systemkrav</title>
</head>

<body>
<div id="page">

<div id="header">
<h1>Yii - Sjekk systemkrav</h1>
</div><!-- header-->

<div id="content">
<h2>Beskrivelse</h2>
<p>
Dette skriptet sjekker om serverkonfigurasjonen oppfyller kravene for å kjøre 
<a href="https://www.yiiframework.com/">Yii</a>-applikasjoner.
Det sjekker om serveren kjører riktig versjon av PHP, om nødvendige extensions
er lastet og om PHP-innstillingene i php.ini er korrekt.
</p>

<h2>Konklusjon</h2>
<p>
<?php if($result>0): ?>
Gratulerer! Konfigurasjonen på serveren tilfredstiller alle krav for å kjøre Yii.
<?php elseif($result<0): ?>
Konfigurasjonen på serveren tilfredstiller minimumskravene til Yii. Vær oppmerksom
på advarslene listet nedenfor dersom applikasjonen din trenger noe av denne 
funksjonaliteten.
<?php else: ?>
Desverre tilfredstiller ikke konfigurasjonen av serveren minimumskravene til Yii.
<?php endif; ?>
</p>

<h2>Detaljer</h2>

<table class="result">
<tr><th>Navn</th><th>Resultat</th><th>Kreves av</th><th>Notat</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
	<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'ok' : ($requirement[1] ? 'feil' : 'advarsel'); ?>">
	<?php echo $requirement[2] ? 'Ok' : 'Feil'; ?>
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
<td class="failed">&nbsp;</td><td>feil</td>
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