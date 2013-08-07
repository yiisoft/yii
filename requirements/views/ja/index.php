<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="en"/>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>Yii 必要条件チェッカ</title>
</head>

<body>
<div id="page">

<div id="header">
<h1>Yii 必要条件チェッカ</h1>
</div><!-- header-->

<div id="content">
<h2>説明</h2>
<p>
このスクリプトは、あなたのサーバ構成が <a href="http://www.yiiframework.com/">Yii</a>
ウェブアプリケーションを実行する必要条件を満たしているかどうかを確認するものです。
すなわち、サーバが正しいバージョンの PHP を実行しているか、適切な PHP 拡張をロードしているか、php.ini ファイルの設定が正しいか、を確認します。</p>

<h2>判定結果</h2>
<p>
<?php if($result>0): ?>
おめでとうございます。あなたのサーバ構成は Yii の全ての必要条件を満しています。
<?php elseif($result<0): ?>
あなたのサーバ構成は Yii の最低限の必要条件を満しています。
「注意」が出ている項目について、あなたのアプリケーションが対応する機能を使用する予定が有るか無いかを確認してください。
<?php else: ?>
残念ですが、あなたのサーバ構成は Yii の必要条件を満していません。
<?php endif; ?>
</p>

<h2>詳細</h2>

<table class="result">
<tr><th style="width:160px">名称</th><th style="width:50px">結果</th><th>対応する機能</th><th>備考</th></tr>
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
<td class="passed" style="width:20px">&nbsp;</td><td style="width:50px">合格</td>
<td class="failed" style="width:20px">&nbsp;</td><td style="width:50px">不合格</td>
<td class="warning" style="width:20px">&nbsp;</td><td style="width:50px">注意</td>
</tr>
</table>

</div><!-- content -->

<div id="footer">
<?php echo $serverInfo; ?>
</div><!-- footer -->

</div><!-- page -->
</body>
</html>