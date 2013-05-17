<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="en"/>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>Yii - Test prostredia</title>
</head>

<body>
<div id="page">

<div id="header">
<h1>Yii - Test prostredia</h1>
</div><!-- header-->

<div id="content">
<h2>Popis</h2>
<p>
Tento skript preverí, či je konfigurácia vášho servera postačujúca
pre korektné fungovanie aplikácii vytvorených pre <a href="http://www.yiiframework.com/">Yii framework</a>.
Test preveruje správnu verziu PHP, prítomnosť potrebných PHP rozšírení a správne nastavenie 
konfigurácie v php.ini.
</p>

<h2>Záver</h2>
<p>
<?php if($result>0): ?>
Gratulujem! Váš server spĺňa všetky požiadavky potrebné pre korektné fungovanie Yii.
<?php elseif($result<0): ?>
Váš server spĺňa minimálne požiadavky potrebné pre fungovanie Yii. 
Venujte prosím pozornosť upozorneniam uvedeným nižšie v prípade, že vaša aplikácia bude využívať potrebné funkcie.
<?php else: ?>
Bohužiaľ, váš server nespĺňa požiadavky potrebné pre fungovanie Yii.
<?php endif; ?>
</p>

<h2>Detaily</h2>

<table class="result">
<tr><th>Názov</th><th>Výsledok</th><th>Požadované od</th><th>Pozn.</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
	<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
	<?php echo $requirement[2] ? 'OK' : ($requirement[1] ? 'Chyba' : 'Upozornenie'); ?>
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
<td class="passed">&nbsp;</td><td>OK</td>
<td class="failed">&nbsp;</td><td>chyba</td>
<td class="warning">&nbsp;</td><td>upozornenie</td>
</tr>
</table>

</div><!-- content -->

<div id="footer">
<?php echo $serverInfo; ?>
</div><!-- footer -->

</div><!-- page -->
</body>
</html>