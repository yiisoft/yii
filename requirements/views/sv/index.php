<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="en"/>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>Yii - Kontroll systemkrav</title>
</head>

<body>
<div id="page">

<div id="header">
<h1>Yii - Kontroll systemkrav</h1>
</div><!-- header-->

<div id="content">
<h2>Beskrivning</h2>
<p>
Det här skriptet kontrollerar om serverkonfigurationen uppfyller kraven för att köra <a href="http://www.yiiframework.com/">Yii</a> webbapplikationer.
Det kollar att servern kör rätt version av PHP,
att erforderliga  PHP-tillägg är laddade, och att inställningar i filen php.ini är korrekta.
</p>

<h2>Slutsats</h2>
<p>
<?php if($result>0): ?>
Grattis! Serverkonfigurationen uppfyller alla krav som Yii ställer.
<?php elseif($result<0): ?>
Serverkonfigurationen uppfyller minimikraven som Yii ställer. Lägg märke till nedanstående varningar om applikationer behöver använda nämnda finesser.
<?php else: ?>
Tyvärr uppfyller inte serverkonfigurationen minimikraven Yii ställer.
<?php endif; ?>
</p>

<h2>Detaljer</h2>

<table class="result">
<tr><th>Namn</th><th>Resultat</th><th>Krävs för</th><th>Memo</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
	<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
	<?php echo $requirement[2] ? 'Stöds' : ($requirement[1] ? 'Stöd saknas' : 'Warning'); ?>
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
<td class="passed">&nbsp;</td><td>passed</td>
<td class="failed">&nbsp;</td><td>failed</td>
<td class="warning">&nbsp;</td><td>warning</td>
</tr>
</table>

</div><!-- content -->

<div id="footer">
<?php echo $serverInfo; ?>
</div><!-- footer -->

</div><!-- page -->
</body>
</html>