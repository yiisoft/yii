<?php if(!$class->nativePropertyCount) return; ?>
<h2>Property Details</h2>
<?php foreach($class->properties as $property): ?>
<?php if($property->isInherited) continue; ?>
<div class="detailHeader" id="<?php echo $property->name.'-detail'; ?>">
<?php echo $property->name; ?>
<span class="detailHeaderTag">
property
<?php if($property->readOnly) echo ' <em>read-only</em> '; ?>
<?php if(!empty($property->since)): ?>
 (available since v<?php echo $property->since; ?>)
<?php endif; ?>
</span>
</div>

<div class="signature">
<?php echo $this->renderPropertySignature($property); ?>
</div>

<p><?php echo $property->description; ?></p>

<?php $this->renderPartial('seeAlso',array('object'=>$property)); ?>

<?php endforeach; ?>
