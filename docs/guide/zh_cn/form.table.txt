收集表格输入
========================

有时我们想通过批量模式收集用户输入。也就是说，
用户可以为多个模型实例输入信息并将它们一次性提交。
我们将此称为 *表格输入（tabular input）* ，因为这些输入项通常以 HTML 表格的形式呈现。

要使用表格输入，我们首先需要创建或填充一个模型实例数组，取决于我们是想插入还是更新数据。
然后我们从 `$_POST` 变量中提取用户输入的数据并将其赋值到每个模型。和单模型输入稍有不同的一点就是：
我们要使用 `$_POST['ModelClass'][$i]` 提取输入的数据而不是使用 `$_POST['ModelClass']`。

~~~
[php]
public function actionBatchUpdate()
{
	// 假设每一项（item）是一个 'Item' 类的实例，
	// 提取要通过批量模式更新的项
	$items=$this->getItemsToUpdate();
	if(isset($_POST['Item']))
	{
		$valid=true;
		foreach($items as $i=>$item)
		{
			if(isset($_POST['Item'][$i]))
				$item->attributes=$_POST['Item'][$i];
			$valid=$valid && $item->validate();
		}
		if($valid)  // 如果所有项目有效
			// ...则在此处做一些操作
	}
	// 显示视图收集表格输入
	$this->render('batchUpdate',array('items'=>$items));
}
~~~

准备好了这个动作，我们需要继续 `batchUpdate` 视图的工作以在一个 HTML 表格中显示输入项。

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

注意，在上面的代码中我们使用了 `"[$i]name"` 而不是 `"name"` 作为调用 
[CHtml::activeTextField] 时的第二个参数。

如果有任何验证错误，相应的输入项将会自动高亮显示，就像前面我们讲解的单模型输入一样。

<div class="revision">$Id: form.table.txt 2232 2010-06-26 22:42:03Z qiang.xue $</div>