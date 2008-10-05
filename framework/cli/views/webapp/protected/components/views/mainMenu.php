<ul>
<?php foreach($items as $item): ?>
<li><?php echo CHtml::link($item['label'],$item['url'],
	$item['active'] ? array('class'=>'active') : array()); ?></li>
<?php endforeach; ?>
</ul>
