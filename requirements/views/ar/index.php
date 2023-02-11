<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ar" lang="ar">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="ar"/>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>مدقق متطلبات Yii</title>
</head>

<body>
<div id="page">

<div id="header">
<h1>مدقق متطلبات Yii</h1>
</div><!-- header-->

<div id="content">
<h2>الوصف</h2>
<p>
يتحقق هذا السكربت ماإذا كانت إعدادات الخادم لديك تفي بمتطلبات تشغيل تطبيقات الويب لاطار العمل <a href="https://www.yiiframework.com/">Yii</a>.
يتحقق ايضا ما إذا كان الخادم يعمل على الإصدارة  الصحيحة من PHP،
تحميل اللاحقات المناسبة، وأيضا ما إذا كانت إعدادات ملف php.ini صحيحة.
</p>

<h2>النتيجة</h2>
<p>
<?php if($result>0): ?>
تهانينا! إعدادات الخادم لديك مستوفية جميع متطلبات تشغيل Yii.
<?php elseif($result<0): ?>
إعدادات الخادم الخاص بك مستوفية للحد الادنى لمتطلبات تشغيل Yii. الرجاء ملاحظة قائمة التنبيهات أدناه وما إذا كان التطبيق الخاص بك يستخدم أحد هذه المزايا.
<?php else: ?>
لسوء الحظ إعدادات الخادم الخاص بك لا تلبي متطلبات تشغيل Yii.
<?php endif; ?>
</p>

<h2>التفاصيل</h2>

<table class="result">
<tr><th>الاسم</th><th>النتيجة</th><th>مطلوب من قبل</th><th>ملاحظات</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
	<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'نجح' : ($requirement[1] ? 'فشل' : 'تنبيه'); ?>">
	<?php echo $requirement[2] ? 'نجح' : ($requirement[1] ? 'فشل' : 'تنبيه'); ?>
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
<td class="passed">&nbsp;</td><td>نجح</td>
<td class="failed">&nbsp;</td><td>فشل</td>
<td class="warning">&nbsp;</td><td>تنبيه</td>
</tr>
</table>

</div><!-- content -->

<div id="footer">
<?php echo $serverInfo; ?>
</div><!-- footer -->

</div><!-- page -->
</body>
</html>