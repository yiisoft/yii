Coletando Entradas Tabulares
============================

As vezes queremos coletar entradas de usuário em modo batch (em lote, vários ao 
mesmo tempo). Isso é, o usuário entra com informações para diversas instâncias 
de modelos e os envia todos de uma só vez. Chamamos isso de *Entrada Tabular*, 
porque seus campos normalmente são apresentados em uma tabela HTML.

Para trabalhar com entradas tabulares, devemos primeiro criar e preencher um vetor 
de instâncias de modelos, dependendo se estamos inserindo ou atualizando os dados. 
Podemos então recuperar as entradas do usuário a partir da variável `$_POST` e 
atribui-las para cada modelo. Dessa forma, existe uma pequena diferença de quando 
utilizamos um único modelo para entrada; devemos recuperar os dados utilizando 
`$_POST['ClasseDoModelo'][$i]` em vez de `$_POST['ClasseDoModelo']`.

~~~
[php]
public function actionBatchUpdate()
{
	// recupera os itens para atualização em lote
	// assumindo que cada item é instância de um Item
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
		if($valid)  // todos os itens são validos
			// ...faça algo aqui
	}
	// exibe a visão para coletar as entradas tabulares
	$this->render('batchUpdate',array('items'=>$items));
}
~~~

Com a ação pronta, precisamos criar a visão `batchUpdate` para exibir os campos 
em um tabela HTML:

~~~
[php]
<div class="yiiForm">
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
</div><!-- yiiForm -->
~~~

Note no código acima que utilizamos `"[$i]name"` em vez de `"name"` no segundo 
parâmetro ao chamar o método [CHtml::activeTextField].

Se ocorrer algum erro de validação, os campos correspondentes serão identificados 
automaticamente, da mesma forma como ocorre quando utilizamos um único modelo.

<div class="revision">$Id: form.table.txt 1389 2009-09-04 14:03:35Z istvan.beregszaszi $</div>
