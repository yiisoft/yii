<?php if($protected && !$class->protectedPropertyCount || !$protected && !$class->publicPropertyCount) return; ?>

<div class="summary">
<h2><?php echo $protected ? 'Protected Properties' : 'Public Properties'; ?></h2>
<p>
<a href="#" class="toggle">Hide inherited properties</a>
</p>
<table class="summaryTable">
<tr>
  <th>Property</th><th>Type</th><th>Description</th><th>Defined By</th>
</tr>
<?php foreach($class->properties as $property): ?>
<?php if($protected && $property->isProtected || !$protected && !$property->isProtected): ?>
<tr<?php echo $property->isInherited?' class="inherited"':''; ?>>
  <td><?php echo $this->renderSubjectUrl($property->definedBy,$property->name); ?></td>
  <td><?php echo $this->renderTypeUrl($property->type); ?></td>
  <td><?php echo $property->introduction; ?></td>
  <td><?php echo $this->renderTypeUrl($property->definedBy); ?></td>
</tr>
<?php endif; ?>
<?php endforeach; ?>
</table>
</div>