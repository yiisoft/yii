HTMLテーブル式入力の収集
========================

私達はユーザーの入力をバッチモードとして束にして集めたいと思う事
が良くあります。複数モデルのインスタンスの情報を、ユーザーが一度に送信できる
様にする場合です。こういった場合の入力フィールドは、往々にしてHTMLテーブル
で提供されるので、ここではこれを *HTMLテーブル式入力* (tabular input)
 と呼びます。

HTMLテーブル式入力 では、まず初めに、データをインサートするか
アップデートするかに従って、モデルインスタンスの為の配列を作成するか、
（アップデートの際は）データの入った状態にします。その後、
ユーザの入力したデータを `$_POST` 変数から回収し、
それぞれのモデルへ割り当てます。シングルモデルの場合とのわずかな違いは、
入力データの回収の為に、`$_POST['ModelClass']` の代わりに
 `$_POST['ModelClass'][$i]` を用いる点です。

~~~
[php]
public function actionBatchUpdate()
{
	// 一括でアップデートをする為にアイテムを回収します。
	// 各アイテムが 'Item' モデルクラス だと仮定します。
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
		if($valid)  // 全てのアイテムが検証を通る際に
			// 処理する内容をここに…
	}
	// HTMLテーブル式入力 をバッチ(一纏め)で回収する為のビューを表示
	$this->render('batchUpdate',array('items'=>$items));
}
~~~

アクションが準備されたら、HTMLテーブルとして入力フィールドを表示する為の
`batchUpdate` ビューの作成に取り掛かる必要があります。

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

上記では、[CHtml::activeTextField] がコールされた際の二番目のパラメータ
として、`"name"` の代わりに `"[$i]name"` を使用している事
に注意してください。

もし何か検証にエラーがある場合は、シングルモデルでの入力の際
に述べたのと同様に、結びついた入力フィールドが自動的にハイライトされます。

<div class="revision">$Id: form.table.txt 2783 2010-12-28 16:20:41Z qiang.xue $</div>