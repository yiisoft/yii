<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="en"/>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>Yii Anforderungs-Tester</title>
</head>

<body>
<div id="page">

<div id="header">
<h1>Yii Anforderungs-Tester</h1>
</div><!-- header-->

<div id="content">
<h2>Beschreibung</h2>
<p>
Dieses Script ermittelt, ob Ihre Serverkonfiguration die Anforderungen zum
Ausführen von <a href="http://www.yiiframework.com/">Yii</a> Webanwendungen
erfüllt.
Es prüft auf die korrekte PHP Version, ob die benötigten Erweiterungen
geladen wurden und ob die Einstellungen in der php.ini korrekt sind
</p>

<h2>Zusammenfassung</h2>
<p>
<?php if($result>0): ?>
Gratulation! Ihr Server erfüllt alle Anforderungen von Yii.
<?php elseif($result<0): ?>
Ihr Server erfüllt die minimalen Anforderungen von Yii. Bitte beachten Sie die untenstehenden Warnungen, wenn Ihre Anwendungen einige dieser Features verwenden sollen.
 <?php else: ?>
Unglücklicherweise erfüllt Ihr Server die Anforderungen von Yii nicht.
<?php endif; ?>
</p>

<h2>Details</h2>

<table class="result">
<tr><th>Name</th><th>Resultat</th><th>Benötigt von</th><th>Memo</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
	<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
	<?php echo $requirement[2] ? 'Bestanden' : ($requirement[1] ? 'Verfehlt' : 'Warnung'); ?>
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
<td class="passed">&nbsp;</td><td>Bestanden</td>
<td class="failed">&nbsp;</td><td>Verfehlt</td>
<td class="warning">&nbsp;</td><td>Warnung</td>
</tr>
</table>

</div><!-- content -->

<div id="footer">
<?php echo $serverInfo; ?>
</div><!-- footer -->

</div><!-- page -->
</body>
</html>