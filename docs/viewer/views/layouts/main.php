<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->baseUrl?>/css/main.css" />
	<title><?php echo $this->pageTitle?></title>

	<?php if($this->language == 'he' || $this->language == 'ar'): ?>
		<!-- Load another CSS file to display the document in an RTL form -->
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->baseUrl?>/css/rtl.css" />
	<?php endif?>
</head>
<body class="page">
	<div id="container">
		<?php echo $content?>
		<br class="clearfloat" />
		<div id="footer">
			<p>Copyright 2008—2012 &copy <a href="http://www.yiisoft.com">Yii Software LLC</a>
			All Rights Reserved |
			<a href="http://www.yiiframework.com/doc/terms/">Terms of Use</a><br/>
			<?php echo Yii::powered()?></p>
		</div>
	</div>
</body>
</html>