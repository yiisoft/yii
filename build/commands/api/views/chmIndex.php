<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN">
<html>
<head>
<meta name="GENERATOR" content="yiidoc http://www.yiiframework.com">
</head>
<body>
<ul>
<?php foreach($this->classes as $name=>$class): ?>
	<li><object type="text/sitemap">
		<param name="Name" value="<?php echo $class->name; ?>">
		<param name="Local" value="<?php echo $class->name; ?>.html">
		</object>
	<ul>
	<?php foreach($class->properties as $property): ?>
		<?php if(!$property->isInherited): ?>
		<li><object type="text/sitemap">
			<param name="Name" value="<?php echo $property->name; ?>">
			<param name="Local" value="<?php echo $class->name . '.html#' . $property->name; ?>">
			</object>
		<?php endif; ?>
	<?php endforeach; ?>
	<?php foreach($class->events as $event): ?>
		<?php if(!$event->isInherited): ?>
		<li><object type="text/sitemap">
			<param name="Name" value="<?php echo $event->name; ?>">
			<param name="Local" value="<?php echo $class->name . '.html#' . $event->name; ?>">
			</object>
		<?php endif; ?>
	<?php endforeach; ?>
	<?php foreach($class->methods as $method): ?>
		<?php if(!$method->isInherited): ?>
		<li><object type="text/sitemap">
			<param name="Name" value="<?php echo $method->name; ?>()">
			<param name="Local" value="<?php echo $class->name . '.html#' . $method->name; ?>">
			</object>
		<?php endif; ?>
	<?php endforeach; ?>
	</ul>
<?php endforeach; ?>
</ul>
</body>
</html>