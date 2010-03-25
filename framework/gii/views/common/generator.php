<div class="common-generator">
	<div class="buttons">
		<?php echo CHtml::submitButton('Preview',array('name'=>'preview')); ?>

		<?php if($model->status===CCodeModel::STATUS_PREVIEW && !$model->hasErrors()): ?>
			<?php echo CHtml::submitButton('Generate',array('name'=>'generate')); ?>
		<?php endif; ?>
	</div>

	<?php if(!$model->hasErrors()): ?>
		<div class="feedback">
		<?php if($model->status===CCodeModel::STATUS_SUCCESS): ?>
			<div class="success">
				<?php echo $this->getSuccessMessage($model); ?>
			</div>
		<?php elseif($model->status===CCodeModel::STATUS_ERROR): ?>
			<div class="error">
				There was some error when generating the code. Please check the following messages.
			</div>
		<?php endif; ?>

		<?php if(isset($_POST['generate'])): ?>
			<pre class="results"><?php echo $model->renderResults(); ?></pre>
		<?php elseif(isset($_POST['preview'])): ?>
			<?php echo CHtml::hiddenField("answers"); ?>
			<div class="check-all">
				<?php echo CHtml::link('check all','#',array('class'=>'all')); ?> |
				<?php echo CHtml::link('check none','#',array('class'=>'none')); ?>
			</div>
			<table>
				<tr>
					<th class="file">Code File</th>
					<th class="confirm">Generate?</th>
				</tr>
				<?php foreach($model->files as $i=>$file): ?>
				<tr class="<?php echo $file->operation; ?>">
					<td class="file">
						<?php echo CHtml::link(CHtml::encode($file->relativePath), array('code','id'=>$i), array('class'=>'view-code')); ?>
						<?php if($file->operation===CCodeFile::OP_OVERWRITE): ?>
							(<?php echo CHtml::link('diff', array('diff','id'=>$i), array('class'=>'view-code')); ?>)
						<?php endif; ?>
					</td>
					<td class="confirm">
						<?php echo $file->operation===CCodeFile::OP_SKIP ? 'unchanged' : $file->operation; ?>
						<?php echo $model->renderConfirmation($file); ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
		<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
