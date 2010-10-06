<?php if(!$class->nativeMethodCount) return; ?>
<h2>Method Details</h2>

<?php foreach($class->methods as $method): ?>
<?php if($method->isInherited) continue; ?>
<div class="detailHeader" id="<?php echo $this->fixMethodAnchor($method->definedBy,$method->name).'-detail'; ?>">
<?php echo $method->name; ?>()
<span class="detailHeaderTag">
method
<?php if(!empty($method->since)): ?>
(available since v<?php echo $method->since; ?>)
<?php endif; ?>
</span>
</div>

<table class="summaryTable">
<tr><td colspan="3">
<div class="signature2">
<?php echo preg_replace('/\{\{([^\{\}]*?)\|([^\{\}]*?)\}\}\(/','$2(',$method->signature); ?>
</div>
</td></tr>
<?php if(!empty($method->input) || !empty($method->output)): ?>
<?php foreach($method->input as $param): ?>
<tr>
  <td class="paramNameCol">$<?php echo $param->name; ?></td>
  <td class="paramTypeCol"><?php echo $this->renderTypeUrl($param->type); ?></td>
  <td class="paramDescCol"><?php echo $param->description; ?></td>
</tr>
<?php endforeach; ?>
<?php if(!empty($method->output)): ?>
<tr>
  <td class="paramNameCol"><?php echo '{return}'; ?></td>
  <td class="paramTypeCol"><?php echo $this->renderTypeUrl($method->output->type); ?></td>
  <td class="paramDescCol"><?php echo $method->output->description; ?></td>
</tr>
<?php endif; ?>
<?php endif; ?>
</table>

<?php $this->renderPartial('sourceCode',array('object'=>$method)); ?>

<p><?php echo $method->description; ?></p>

<?php $this->renderPartial('seeAlso',array('object'=>$method)); ?>

<?php endforeach; ?>