Tabulär datainmatning
=====================

Ibland vill man kunna samla in användarinmatning satsvis (batch mode). Det vill 
säga, användaren kan mata in information som tillhör flera modellinstanser och 
skicka in dem alla på en gång. Detta kallas *tabulär inmatning* eftersom 
inmatningsfälten ofta organiseras i en HTML-tabell.

För att arbeta med tabulär inmatning behöver vi först skapa eller tilldela 
värden i en array av modellinstanser, beroende på ifall vi infogar eller 
uppdaterar data. Därefter återhämtas användarinmatning från `$_POST`-variabeln 
och tilldelas respektive modellinstans. En liten skillnad mot inmatning av en 
enstaka modellinstans är att vi hämtar in inmatningen med användning av 
`$_POST['ModelClass'][$i]` i stället för `$_POST['ModelClass']`.

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

Med åtgärden på plats behövs arbete på vyn `batchUpdate` för att presentera 
inmatningsfälten i form av en HTML-tabell.

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

Notera att i ovanstående används `"[$i]name"` i stället för `"name"` som andra 
parameter i anropet till [CHtml::activeTextField].

Om det uppstår valideringsfel kommer motsvarande inmatningsfält att 
framhävas automatiskt, precis som tidigare beskrivits i fråga om inmatning av en 
enstaka modellinstans.

<div class="revision">$Id: form.table.txt 2783 2010-12-28 16:20:41Z qiang.xue $</div>