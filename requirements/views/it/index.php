<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="en"/>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>Verifica soddisfazione requisiti di Yii</title>
</head>

<body>
<div id="page">

<div id="header">
<h1>Verifica soddisfazione requisiti di Yii</h1>
</div><!-- header-->

<div id="content">
<h2>Descrizione</h2>
<p>
Questo script verifica se la configurazione del tuo server soddisfa i requisiti 
di esecuzione delle web application sviluppate con <a href="https://www.yiiframework.com/">Yii</a>.
Verifica se nel server è in esecuzione la corretta versione di PHP, se le 
estensioni PHP necessarie sono state caricate e se le impostazioni di php.ini sono corrette.
</p>

<h2>Conclusioni</h2>
<p>
<?php if($result>0): ?>
Congratulazioni! La configurazione del tuo server soddisfa tutti i requisiti di Yii.
<?php elseif($result<0): ?>
La configurazione del tuo server soddisfa i requisiti minimi di Yii. Si prega di prestare attenzione agli avvisi qui sotto qualora l'applicazione utilizzi le corrispondenti funzionalità.
<?php else: ?>
Sfortunatamente La configurazione del tuo server non soddisfa i requisiti di Yii.
<?php endif; ?>
</p>

<h2>Dettagli</h2>

<table class="result">
<tr><th>Nome</th><th>Resultato</th><th>Richiesto da</th><th>Memo</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
	<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
	<?php echo $requirement[2] ? 'Superato' : 'Fallito'; ?>
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
<td class="passed">&nbsp;</td><td>superato</td>
<td class="failed">&nbsp;</td><td>fallito</td>
<td class="warning">&nbsp;</td><td>avviso</td>
</tr>
</table>

</div><!-- content -->

<div id="footer">
<?php echo $serverInfo; ?>
</div><!-- footer -->

</div><!-- page -->
</body>
</html>