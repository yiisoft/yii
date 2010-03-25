<?php
if(($pos=strrpos($file->path,'.'))!==false)
	$type=substr($file->path,$pos+1);
else
	$type='unknown';
?>
<div class="title">
	<div class="buttons">
		<a href="#" class="close-code">Close</a>&nbsp;
	</div>
	<?php echo CHtml::encode($file->path); ?>
</div>

<br/>

<?php
if($type==='php')
{
	echo '<div class="content">';
	highlight_string($file->content);
	echo '</div>';
}
else if($type==='txt')
{
	echo '<div class="content">';
	echo nl2br($file->content);
	echo '</div>';
}
else
	echo '<div class="error">Preview is not available for this file type.</div>';
?>