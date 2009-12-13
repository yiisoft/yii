<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="en"/>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>Verificare cerinte Yii</title>
</head>

<body>
<div id="page">

<div id="header">
<h1>Verificarea cerintelor Yii</h1>
</div><!-- header-->

<div id="content">
<h2>Descriere</h2>
<p>
Acest script verifica daca configuratia serverului dvs indeplineste cerintele 
pentru rularea aplicatiilor Web <a href="http://www.yiiframework.com/">Yii</a>. 
Se fac urmatoarele verificari: daca serverul ruleaza versiunea corecta de PHP, 
daca extensiile PHP corespunzatoare au fost incarcate si daca 
setarile din fisierul php.ini sunt corecte.
</p>

<h2>Concluzie</h2>
<p>
<?php if($result>0): ?>
Felicitari! Configuratia serverului dvs indeplineste toate cerintele Yii.
<?php elseif($result<0): ?>
Configuratia serverului dvs indeplineste cerintele minime pentru Yii. 
Va rugam sa cititi avertismentele afisate mai jos, in cazul in care aplicatia pe care o veti crea 
va folosi respectivele feature-uri.
<?php else: ?>
Din pacate, configuratia serverului dvs nu indeplineste cerintele Yii.
<?php endif; ?>
</p>

<h2>Detalii</h2>

<table class="result">
<tr><th>Nume</th><th>Rezultat</th><th>Cerut de</th><th>Precizari</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
	<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
	<?php echo $requirement[2] ? 'Passed' : 'Failed'; ?>
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