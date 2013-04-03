<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="es"/>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>Verificació de requerimients de Yii</title>
</head>

<body>
<div id="page">

<div id="header">
<h1>Verificació de requerimients de Yii</h1>
</div><!-- header-->

<div id="content">
<h2>Descripció</h2>
<p>
Aquest script verifica que la configuració del seu servidor compleix amb els requeriments necessàris per poder executar aplicacions Web <a href="http://www.yiiframework.com/">Yii</a>.
El mateix verifica que el servidor corre una versió adequada de PHP, que les extensions PHP necessàries han set carregades i que les configuracions de l'arxiu php.ini són correctes.
</p>

<h2>Conclusió</h2>
<p>
<?php if($result>0): ?>
Enhorabona! El vostre servidor compleix tots els requeriments de Yii.
<?php elseif($result<0): ?>
La configuració del vostre servidor compleix els requeriments mínims de Yii. Si us plau, prengui atenció a les advertències llistades a continuació si la seva aplicació utilitza alguna d'aquestes característiques.
<?php else: ?>
Desafortunadamente la configuración de su servidor no satisface los requerimientos de Yii.
Desafortunamadament, la configuració del vostre servidor no compleix els requeriments de Yii.
<?php endif; ?>
</p>

<h2>Detalles</h2>

<table class="result">
<tr><th>Nom</th><th>Resultat</th><th>Requerit per</th><th>Memo</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
	<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
	<?php echo $requirement[2] ? 'Correcte' : 'Error'; ?>
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
<td class="passed">&nbsp;</td><td>correcte</td>
<td class="failed">&nbsp;</td><td>error</td>
<td class="warning">&nbsp;</td><td>advertència</td>
</tr>
</table>

</div><!-- content -->

<div id="footer">
<?php echo $serverInfo; ?>
</div><!-- footer -->

</div><!-- page -->
</body>
</html>