<h1>All Classes</h1>

<table class="summaryTable">
<tr>
  <th>Package</th><th>Class</th><th>Description</th>
</tr>
<?php foreach($this->packages as $package=>$classes): ?>
<?php foreach($classes as $i=>$class): ?>
<tr>
<?php if(!$i): ?>
  <td rowspan="<?php echo count($classes); ?>"><?php echo '<a name="'.$package.'"></a>' . $package; ?></td>
<?php endif; ?>
  <td><?php echo '{{'.$class.'|'.$class.'}}'; ?></td>
  <td><?php echo $this->classes[$class]->introduction; ?></td>
</tr>
<?php endforeach; ?>
<?php endforeach; ?>
</table>
