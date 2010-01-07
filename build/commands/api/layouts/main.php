<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="language" content="en" />
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="css/api.css" />
<script type="text/javascript" src="js/jquery.js"></script>
<title><?php echo $this->pageTitle; ?></title>
</head>

<body>
<div id="page">

<div id="header">
<a href="http://www.yiiframework.com">Yii Framework</a> v<?php echo Yii::getVersion(); ?> Class Reference
</div><!-- end of header -->

<div id="content">
<?php echo $content; ?>
</div><!-- end of content -->

<div id="footer">
Copyright &copy; 2008-2010 by <a href="http://www.yiisoft.com">Yii Software LLC</a><br/>
All Rights Reserved.<br/>
</div><!-- end of footer -->

<script type="text/javascript">
/*<![CDATA[*/
$("a.toggle").toggle(function(){
	$(this).text($(this).text().replace(/Hide/,'Show'));
	var a=$(this).parents(".summary");
	a.find(".inherited").hide();
},function(){
	$(this).text($(this).text().replace(/Show/,'Hide'));
	$(this).parents(".summary").find(".inherited").show();
});
/*]]>*/
</script>

</div><!-- end of page -->
</body>

</html>