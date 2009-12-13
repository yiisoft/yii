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
<h1>Yii要求チェッカ</h1>
</div><!-- header-->

<div id="content">
<h2>説明</h2>
<p>
このスクリプトは、あなたのサーバ構成が、<a href="http://www.yiiframework.com/">Yii</a>アプリケーション
を実行するための要求を満たしているかどうかを確認します。
このスクリプトはサーバにおいて正しいバージョンのPHPを実行しているか、適切なPHP拡張をロードしているか、php.iniファイル設定が正しいか
を確認します。
</p>

<h2>結果</h2>
<p>
<?php if($result>0): ?>
おめでとうございます。あなたのサーバ構成はYiiの全ての要求を満しています。
<?php elseif($result<0): ?>
あなたのサーバ構成はYiiの最低限の要求を満しています。もしあなたのアプリケーションが対応する機能を
使用している場合には以下の警告に注意してください。
<?php else: ?>
申し訳ありませんが、あなたのサーバ構成はYiiの要求を満していません。
<?php endif; ?>
</p>

<h2>詳細</h2>

<table class="result">
<tr><th>名称</th><th>結果</th><th>必要元</th><th>備考</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
	<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
	<?php echo $requirement[2] ? '合格' : ($requirement[1] ? '不合格' : '注意'); ?>
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
<td class="passed">&nbsp;</td><td>合格</td>
<td class="failed">&nbsp;</td><td>不合格</td>
<td class="warning">&nbsp;</td><td>注意</td>
</tr>
</table>

</div><!-- content -->

<div id="footer">
<?php echo $serverInfo; ?>
</div><!-- footer -->

</div><!-- page -->
</body>
</html>