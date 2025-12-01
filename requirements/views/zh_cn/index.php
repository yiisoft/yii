<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="en"/>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>Yii配置需求检查</title>
</head>

<body>
<div id="page">

<div id="header">
<h1>Yii配置需求检查</h1>
</div><!-- header-->

<div id="content">
<h2>检查内容</h2>
<p>
本网页用于确认您的服务器配置是否能满足运行<a href="https://www.yiiframework.com/">Yii</a> Web应用的要求。它将检查服务器所运行的PHP版本，查看是否安装了合适的PHP扩展模块，以及确认php.ini文件是否正确设置。
</p>

<h2>检查结果</h2>
<p>
<?php if($result>0): ?>
恭喜！您的服务器配置完全符合Yii的要求。
<?php elseif($result<0): ?>
您的服务器配置符合Yii的最低要求。如果您需要使用特定的功能，请关注如下警告。
<?php else: ?>
您的服务器配置未能满足Yii的要求。
<?php endif; ?>
</p>

<h2>具体结果</h2>

<table class="result">
<tr><th>项目名称</th><th>结果</th><th>使用者</th><th>备注</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
	<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
	<?php echo $requirement[2] ? '通过' : '未通过'; ?>
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
<td class="passed">&nbsp;</td><td>通过</td>
<td class="failed">&nbsp;</td><td>未通过</td>
<td class="warning">&nbsp;</td><td>警告</td>
</tr>
</table>

</div><!-- content -->

<div id="footer">
<?php echo $serverInfo; ?>
</div><!-- footer -->

</div><!-- page -->
</body>
</html>