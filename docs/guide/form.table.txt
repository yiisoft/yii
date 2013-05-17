Collecting Tabular Input
========================

Sometimes we want to collect user input in a batch mode. That is, the user
can enter the information for multiple model instances and submit them all
at once. We call this *tabular input* because the input fields are
often presented in an HTML table.

To work with tabular input, we first need to create or populate an array
of model instances, depending on whether we are inserting or updating the
data. We then retrieve the user input data from the `$_POST` variable and
assign it to each model. A slight difference from single model input is
that we retrieve the input data using `$_POST['ModelClass'][$i]` instead of
`$_POST['ModelClass']`.

~~~
[php]
public function actionBatchUpdate()
{
	// retrieve items to be updated in a batch mode
	// assuming each item is of model class 'Item'
	$items=$this->getItemsToUpdate();
	if(isset($_POST['Item']))
	{
		$valid=true;
		foreach($items as $i=>$item)
		{
			if(isset($_POST['Item'][$i]))
				$item->attributes=$_POST['Item'][$i];
			$valid=$item->validate() && $valid;
		}
		if($valid)  // all items are valid
			// ...do something here
	}
	// displays the view to collect tabular input
	$this->render('batchUpdate',array('items'=>$items));
}
~~~

Having the action ready, we need to work on the `batchUpdate` view to
display the input fields in an HTML table.

~~~
[php]
<div class="form">
<?php echo CHtml::beginForm(); ?>
<table>
<tr><th>Name</th><th>Price</th><th>Count</th><th>Description</th></tr>
<?php foreach($items as $i=>$item): ?>
<tr>
<td><?php echo CHtml::activeTextField($item,"[$i]name"); ?></td>
<td><?php echo CHtml::activeTextField($item,"[$i]price"); ?></td>
<td><?php echo CHtml::activeTextField($item,"[$i]count"); ?></td>
<td><?php echo CHtml::activeTextArea($item,"[$i]description"); ?></td>
</tr>
<?php endforeach; ?>
</table>

<?php echo CHtml::submitButton('Save'); ?>
<?php echo CHtml::endForm(); ?>
</div><!-- form -->
~~~

Note in the above that we use `"[$i]name"` instead of `"name"` as the
second parameter when calling [CHtml::activeTextField].

If there are any validation errors, the corresponding input fields will
be highlighted automatically, just like the single model input we described
earlier on.

<div class="revision">$Id$</div>