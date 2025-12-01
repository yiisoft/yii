<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="en"/>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>Yii Requirement Checker</title>
</head>

<body>
<div id="page">

<div id="header">
<h1>Проверка на изискванията на Yii</h1>
</div><!-- header-->

<div id="content">
<h2>Описание</h2>
<p>
This script checks if your server configuration meets the requirements
for running <a href="https://www.yiiframework.com/">Yii</a> Web applications.
It checks if the server is running the right version of PHP,
if appropriate PHP extensions have been loaded, and if php.ini file settings are correct.

Този скрипт проверява дали конфигурацията на вавият сървър, отговаря на изискванията за
работа на <a href="https://www.yiiframework.com/">Yii</a> уеб приложенията.
Проверява дали на сървъра работи подходящата версия на PHP,
дали подходящите PHP добавки са заредени и дали настройките в php.ini са вярни
</p>

<h2>Заключение</h2>
<p>
<?php if($result>0): ?>
Congratulations! Your server configuration satisfies all requirements by Yii.
Поздравление! Вашият сървър отговаря на всики изисквания за работа на Yii.
<?php elseif($result<0): ?>
Вашият сървър удоблетворява минималните изисквания на Yii. Моля, обърнете внимание на списъка с грешки отдоло, ако вашето приложение ще използва тези функции.
<?php else: ?>
За жалост, вашият сървър, не отговаря на изискванията за работа на Yii.
<?php endif; ?>
</p>

<h2>Детайли</h2>

<table class="result">
<tr><th>Име</th><th>Резултат</th><th>Поискано от</th><th>Бележка</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
	<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
	<?php echo $requirement[2] ? 'Passed' : ($requirement[1] ? 'Failed' : 'Warning'); ?>
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
<td class="passed">&nbsp;</td><td>преминат</td>
<td class="failed">&nbsp;</td><td>провален</td>
<td class="warning">&nbsp;</td><td>предупреждение</td>
</tr>
</table>

</div><!-- content -->

<div id="footer">
<?php echo $serverInfo; ?>
</div><!-- footer -->

</div><!-- page -->
</body>
</html>
