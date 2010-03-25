<div class="title">
	<div class="buttons">
		<a href="#" class="close-code">Close</a>&nbsp;
	</div>
	<?php echo CHtml::encode($file->path); ?>
</div>

<br/>

<?php if($diff===false): ?>
	<div class="error">Diff is not supported for this file type.</div>
<?php elseif(empty($diff)): ?>
	<div class="error">No changes.</div>
<?php else: ?>
	<div class="content">
		<pre class="diff"><?php echo $diff; ?></pre>
	</div>
<?php endif; ?>
