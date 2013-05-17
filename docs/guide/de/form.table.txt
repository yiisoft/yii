Erfassen tabellarischer Eingaben
================================

Manchmal muss man eine ganze Reihe gleichartiger Eingabewerten erfasst werden. 
Der Anwender kann also die Daten für mehrere Modelinstanzen auf einmal
eingeben und absenden. Dies nennt man *tabellarische Eingabe*, weil die
Eingabefelder in diesem Fall oft in einer Tabelle dargestellt werden.

Für solche tabellarische Eingaben muss zunächst ein Array mit Modelobjekten
erstellt bzw. befüllt werden, je nachdem ob die Daten eingefügt oder 
aktualisiert werden sollen. Die gesendeten Formulardaten werden dann
nacheinander aus `$_POST` gelesen und in den jeweiligen Modelinstanzen 
zugewiesen. Im Gegensatz zum vorher beschriebenen Verfahren bei einem einzelnen Model
werden die Daten hier jeweils aus `$_POST['ModelClass'][$i]` statt 
`$_POST['ModelClass']` gelesen.

~~~
[php]
public function actionBatchUpdate()
{
	// Zu aktualisierende Datensätze abfragen
    // Die Datensätze seien vom Modeltyp 'Item'
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
		if($valid)  // alle Daten sind gültig
			// ...Daten werden hier weiter verarbeitet
	}
	// Zeigt den View an und erfasst tabellarische Eingabe
	$this->render('batchUpdate',array('items'=>$items));
}
~~~

Ist die Action entsprechend erstellt, müssen im View `batchUpdate`
die Eingabefelder in einer HTML-Tabelle angezeigt werden.

~~~
[php]
<div class="form">
<?php echo CHtml::beginForm(); ?>
<table>
<tr><th>Name</th><th>Preis</th><th>Anzahl</th><th>Beschreibung</th></tr>
<?php foreach($items as $i=>$item): ?>
<tr>
<td><?php echo CHtml::activeTextField($item,"[$i]name"); ?></td>
<td><?php echo CHtml::activeTextField($item,"[$i]price"); ?></td>
<td><?php echo CHtml::activeTextField($item,"[$i]count"); ?></td>
<td><?php echo CHtml::activeTextArea($item,"[$i]description"); ?></td>
</tr>
<?php endforeach; ?>
</table>

<?php echo CHtml::submitButton('Speichern'); ?>
<?php echo CHtml::endForm(); ?>
</div><!-- form -->
~~~

Beachten Sie, dass beim Aufruf von [CHtml::activeTextField] `"[$i]name"` 
statt `"name"` verwendet wird.

Genau wie beim View für einfache Models werden auch hier die fehlerhaften
Eingabefelder automatisch hervorgehoben.

<div class="revision">$Id: form.table.txt 2783 2010-12-28 16:20:41Z qiang.xue $</div>
