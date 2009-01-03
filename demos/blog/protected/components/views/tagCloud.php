<?php foreach(Tag::model()->findTagWeights() as $tag=>$weight): ?>

<span class="tag" style="font-size:<?php echo $weight; ?>pt"><?php echo CHtml::link(CHtml::encode($tag),array('post/list','tag'=>$tag)); ?></span>
<?php endforeach; ?>