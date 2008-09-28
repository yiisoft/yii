<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN">
<html>
<head>
<meta name="GENERATOR" content="yiidoc http://www.yiiframework.com">
</head>
<body>
<object type="text/site properties">
<param name="Window Styles" value="0x800025">
<param name="FrameName" value="right">
<param name="ImageType" value="Folder">
<param name="comment" value="title:Online Help">
<param name="comment" value="base:index.html">
</object>

<ul>
	<li><object type="text/sitemap">
		<param name="Name" value="All Packages">
		<param name="Local" value="index.html">
		</object>
<?php foreach($this->packages as $package=>$classes): ?>
	<li><object type="text/sitemap">
		<param name="Name" value="<?php echo $package; ?>">
		</object>
	<ul>
	<?php foreach($classes as $class): ?>
		<li><object type="text/sitemap">
			<param name="Name" value="<?php echo $class; ?>">
			<param name="Local" value="<?php echo $class; ?>.html">
			</object>
	<?php endforeach; ?>
	</ul>
<?php endforeach; ?>
</ul>
</body>
</html>