<table class="summaryTable docClass">
<colgroup>
	<col class="col-name" />
	<col class="col-value" />
</colgroup>
<tr>
  <th>Package</th>
  <td><?php echo '{{index::'.$class->package.'|'.$class->package.'}}'; ?></td>
</tr>
<tr>
  <th>Inheritance</th>
  <td><?php echo $this->renderInheritance($class); ?></td>
</tr>
<?php if(!empty($class->interfaces)): ?>
<tr>
  <th>Implements</th>
  <td><?php echo $this->renderImplements($class); ?></td>
</tr>
<?php endif; ?>
<?php if(!empty($class->subclasses)): ?>
<tr>
  <th>Subclasses</th>
  <td><?php echo $this->renderSubclasses($class); ?></td>
</tr>
<?php endif; ?>
<?php if(!empty($class->since)): ?>
<tr>
  <th>Since</th>
  <td><?php echo $class->since; ?></td>
</tr>
<?php endif; ?>
<?php if(!empty($class->version)): ?>
<tr>
  <th>Version</th>
  <td><?php echo $class->version; ?></td>
</tr>
<?php endif; ?>
<tr>
  <th>Source Code</th>
  <td><?php echo $this->renderSourceLink($class->sourcePath); ?></td>
</tr>
</table>

<div id="classDescription">
<?php echo $class->description; ?>
</div>