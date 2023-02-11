<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="en"/>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>Yii系統需求檢查</title>
</head>

<body>
<div id="page">

<div id="header">
<h1>Yii系統需求檢查</h1>
</div><!-- header-->

<div id="content">
<h2>檢查內容說明</h2>
<p>
本網頁用於確認您的伺服器組態設定是否能滿足執行<a href="https://www.yiiframework.com/">Yii</a> Web應用程式的要求. 它將檢查伺服器是否使用正確的PHP版本, 是否安裝了合適的PHP extension, 以及確認php.ini檔案是否正確設定.
</p>

<h2>檢查結果</h2>
<p>
<?php if($result>0): ?>
恭喜! 您的伺服器組態設定完全符合Yii的要求.
<?php elseif($result<0): ?>
您的伺服器組態設定符合Yii的最低要求. 請注意下列警告(如果您的應用程式會需要使用到相關功能).
<?php else: ?>
您的伺服器組態設定未能滿足Yii的要求.
<?php endif; ?>
</p>

<h2>詳細結果</h2>

<table class="result">
<tr><th>項目名稱</th><th>結果</th><th>使用者</th><th>備註</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
	<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
	<?php echo $requirement[2] ? '通過' : '未通過'; ?>
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
<td class="passed">&nbsp;</td><td>通過</td>
<td class="failed">&nbsp;</td><td>未通過</td>
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