Colectare input-uri tabulare
================================

Uneori vrem sa colectam date de la utilizator in mod automat. Adica, utilizatorul
poate introduce informatiile pentru mai multe instante de modele si sa le trimita pe toate
o data. Denumim aceasta modalitate *input tabular* deoarece campurile input sunt de obicei
prezentate intr-un tabel HTML.

Pentru a folosi un input tabular, trebuie intai sa cream si sa populam un array
de instante cu modelele respective, in functie ce trebuie sa facem, inserare sau actualizare
de date. Apoi, trebuie sa extragem datele primite de la utilizator din variabila `$_POST`
si sa asignam aceste date fiecarui model. O diferenta mica fata de asignarea in cazul
unui singur model, este ca extragem datele de intrare folosind `$_POST['ModelClass'][$i]` in loc de
`$_POST['ModelClass']`.

~~~
[php]
public function actionBatchUpdate()
{
	// extragem elementele de actualizat automat
	// presupunem ca fiecare element este al clasei modelului 'Item'
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
		if($valid)  // toate elementele sunt valide
			// ...se executa ceva aici
	}
	// afisam view-ul pentru colectarea input-ului tabular
	$this->render('batchUpdate',array('items'=>$items));
}
~~~

Avand action-ul pregatit, trebuie sa cream view-ul `batchUpdate` pentru a
afisa campurile input intr-o tabela HTML.

~~~
[php]
<div class="yiiForm">
<?php echo CHtml::form(); ?>
<table>
<tr><th>Name</th><th>Price</th><th>Count</th><th>Description</th></tr>
<?php foreach($items as $i=>$item): ?>
<tr>
<td><?php echo CHtml::activeTextField($item,"name[$i]"); ?></td>
<td><?php echo CHtml::activeTextField($item,"price[$i]"); ?></td>
<td><?php echo CHtml::activeTextField($item,"count[$i]"); ?></td>
<td><?php echo CHtml::activeTextArea($item,"description[$i]"); ?></td>
</tr>
<?php endforeach; ?>
</table>

<?php echo CHtml::submitButton('Save'); ?>
</form>
</div><!-- yiiForm -->
~~~

Trebuie notat ca folosim `"name[$i]"` in loc de `"name"` cand apelam [CHtml::activeTextField].

Daca este vreo eroare de validare, campurile input corespunzatoare vor fi evidentiate
automat, in acelasi fel ca in cazul input-urilor unui singur model, caz descris mai devreme.

<div class="revision">$Id: form.table.txt 468 2009-01-04 20:57:35Z qiang.xue $</div>