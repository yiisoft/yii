<h2>Manage Posts</h2>

<table class="dataGrid">
  <tr>
    <th><?php echo $sort->link('status'); ?></th>
    <th><?php echo $sort->link('title'); ?></th>
    <th><?php echo $sort->link('createTime'); ?></th>
    <th><?php echo $sort->link('updateTime'); ?></th>
  </tr>
<?php foreach($posts as $n=>$post): ?>
  <tr class="<?php echo $n%2?'even':'odd';?>">
    <td><?php echo CHtml::encode($post->statusText); ?></td>
    <td><?php echo CHtml::link(CHtml::encode($post->title),array('show','id'=>$post->id)); ?></td>
    <td><?php echo date('F j, Y',$post->createTime); ?></td>
    <td><?php echo date('F j, Y',$post->updateTime); ?></td>
  </tr>
<?php endforeach; ?>
</table>

<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>