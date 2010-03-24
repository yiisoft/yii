<h1>Welcome to Yii Code Generator!</h1>

<p>
	You may use the following generators to quickly generate the code that can build up your Yii application:
</p>
<ul>
	<?php foreach($this->module->controllerMap as $name=>$config): ?>
	<li><?php echo CHtml::link(ucwords(CHtml::encode($name).' generator'),array('/gii/'.$name));?></li>
	<?php endforeach; ?>
</ul>

