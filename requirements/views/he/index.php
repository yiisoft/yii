<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir='rtl' xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="en"/>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>בדיקת דרישות מינימום להרצת Yii</title>
</head>

<body>
<div id="page">

<div id="header">
<h1>בדיקת דרישות מינימום להרצת Yii</h1>
</div><!-- header-->

<div id="content">
<h2>תיאור</h2>
<p>
הסקריפט הבא בודק אם סביבת השרת שלך תוכל לעמוד בדרישות המינימום כדי להריץ את 
 <a href="http://www.yiiframework.com/">Yii</a> לפיתוח מערכות ווב מתקדמות.
הסקריפט בודק אם השרת מריץ גרסאת PHP עדכנית,
אם הרחבות מסויימת קיימות ב PHP ונטענו בהצלחה, ואם הגדרות ה php.ini מוגדרות כמו שצריך.
</p>

<h2>סיכום</h2>
<p>
<?php if($result>0): ?>
ברכותיינו! סביבת השרת שלך תומכת בכל הגדרישות של Yii.
<?php elseif($result<0): ?>
הגדרות סביבת השרת שלך מתאימות לדרישות המינימום של Yii.
 אנא שים לב לאזהרות והערות הכתובות מטה במידה והאפליקציה שתכתוב תשתמש באחת מהאפשרויות הללו.
<?php else: ?>
לצערנו סביבת השרת שלך אינה תומכת בדרישות המינימום של Yii.
<?php endif; ?>
</p>

<h2>פרטים</h2>

<table class="result">
<tr><th>כותרת</th><th>תוצאה</th><th>נדרש על ידי</th><th>תזכורת</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
	<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
	<?php echo $requirement[2] ? 'עבר' : 'נכשל'; ?>
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
<td class="passed">&nbsp;</td><td>עבר</td>
<td class="failed">&nbsp;</td><td>נכשל</td>
<td class="warning">&nbsp;</td><td>אזהרה</td>
</tr>
</table>

</div><!-- content -->

<div id="footer">
<?php echo $serverInfo; ?>
</div><!-- footer -->

</div><!-- page -->
</body>
</html>