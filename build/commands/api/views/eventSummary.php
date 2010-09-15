<?php if(empty($class->events)) return; ?>

<div class="summary">
<h2>Events</h2>

<p><a href="#" class="toggle">Hide inherited events</a></p>

<table class="summaryTable">
<tr>
  <th>Event</th><th>Description</th><th>Defined By</th>
</tr>
<?php foreach($class->events as $event): ?>
<tr<?php echo $event->isInherited?' class="inherited"':''; ?> id="<?php echo $event->name; ?>">
  <td><?php echo $this->renderSubjectUrl($event->definedBy,$event->name); ?></td>
  <td><?php echo $event->introduction; ?></td>
  <td><?php echo $this->renderTypeUrl($event->definedBy); ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>