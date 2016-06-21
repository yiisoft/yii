<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="en"/>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>Перевірка на відповідність до вимог Yii</title>
</head>

<body>
<div id="page">

<div id="header">
<h1>Перевірка на відповідність до вимог Yii</h1>
</div><!-- header-->

<div id="content">
<h2>Опис</h2>
<p>
Цей скрипт перевіряє чи відповідає конфігурація Вашого веб-сервера вимогам,
що висуваються до <a href="http://www.yiiframework.com/">Yii</a> веб-додатків.
Зокрема перевіряється версія PHP, чи завантажені необхідні розширення PHP,
а також коректність налаштувань у файлі php.ini.
</p>

<h2>Висновок</h2>
<p>
<?php if($result>0): ?>
Вітаємо! Конфігурація  Вашого веб-сервера задовольняє всі вимоги Yii.
<?php elseif($result<0): ?>
Конфігурація Вашого веб-сервера задовольняє мінімально необхідні вимоги Yii. Зверніть увагу на попередження у табличці нижче, якщо передбачається використання відповідних функцій.
<?php else: ?>
На жаль, конфігурація Вашого веб-сервера не задовольняє вимоги Yii.
<?php endif; ?>
</p>

<h2>Результати перевірки</h2>

<table class="result">
<tr><th>Назва</th><th>Результат</th><th>Вимагається для</th><th>Пояснення</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
		<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
		<?php echo $requirement[2] ? 'Так' : ($requirement[1] ? 'Ні' : 'Попередження'); ?>
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
<td class="passed">&nbsp;</td><td>Так</td>
<td class="failed">&nbsp;</td><td>Ні</td>
<td class="warning">&nbsp;</td><td>Попередження</td>
</tr>
</table>

</div><!-- content -->

<div id="footer">
<?php echo $serverInfo; ?>
</div><!-- footer -->

</div><!-- page -->
</body>
</html>